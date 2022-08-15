<?php

namespace Kirby\Filesystem;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The `Dir` class provides methods
 * for dealing with directories on the
 * file system level, like creating,
 * listing, moving, copying or
 * evaluating directories etc.
 *
 * For the Cms, it includes methods,
 * that handle scanning directories
 * and converting the results into
 * children, files and other page stuff.
 *
 * @package   Kirby Filesystem
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Dir
{
	/**
	 * Ignore when scanning directories
	 *
	 * @var array
	 */
	public static $ignore = [
		'.',
		'..',
		'.DS_Store',
		'.gitignore',
		'.git',
		'.svn',
		'.htaccess',
		'Thumb.db',
		'@eaDir'
	];

	public static $numSeparator = '_';

	/**
	 * Copy the directory to a new destination
	 *
	 * @param string $dir
	 * @param string $target
	 * @param bool $recursive
	 * @param array $ignore
	 * @return bool
	 */
	public static function copy(string $dir, string $target, bool $recursive = true, array $ignore = []): bool
	{
		if (is_dir($dir) === false) {
			throw new Exception('The directory "' . $dir . '" does not exist');
		}

		if (is_dir($target) === true) {
			throw new Exception('The target directory "' . $target . '" exists');
		}

		if (static::make($target) !== true) {
			throw new Exception('The target directory "' . $target . '" could not be created');
		}

		foreach (static::read($dir) as $name) {
			$root = $dir . '/' . $name;

			if (in_array($root, $ignore) === true) {
				continue;
			}

			if (is_dir($root) === true) {
				if ($recursive === true) {
					static::copy($root, $target . '/' . $name, true, $ignore);
				}
			} else {
				F::copy($root, $target . '/' . $name);
			}
		}

		return true;
	}

	/**
	 * Get all subdirectories
	 *
	 * @param string $dir
	 * @param array $ignore
	 * @param bool $absolute
	 * @return array
	 */
	public static function dirs(string $dir, array $ignore = null, bool $absolute = false): array
	{
		$result = array_values(array_filter(static::read($dir, $ignore, true), 'is_dir'));

		if ($absolute !== true) {
			$result = array_map('basename', $result);
		}

		return $result;
	}

	/**
	 * Checks if the directory exists on disk
	 *
	 * @param string $dir
	 * @return bool
	 */
	public static function exists(string $dir): bool
	{
		return is_dir($dir) === true;
	}

	/**
	 * Get all files
	 *
	 * @param string $dir
	 * @param array $ignore
	 * @param bool $absolute
	 * @return array
	 */
	public static function files(string $dir, array $ignore = null, bool $absolute = false): array
	{
		$result = array_values(array_filter(static::read($dir, $ignore, true), 'is_file'));

		if ($absolute !== true) {
			$result = array_map('basename', $result);
		}

		return $result;
	}

	/**
	 * Read the directory and all subdirectories
	 *
	 * @param string $dir
	 * @param bool $recursive
	 * @param array $ignore
	 * @param string $path
	 * @return array
	 */
	public static function index(string $dir, bool $recursive = false, array $ignore = null, string $path = null)
	{
		$result = [];
		$dir    = realpath($dir);
		$items  = static::read($dir);

		foreach ($items as $item) {
			$root     = $dir . '/' . $item;
			$entry    = $path !== null ? $path . '/' . $item : $item;
			$result[] = $entry;

			if ($recursive === true && is_dir($root) === true) {
				$result = array_merge($result, static::index($root, true, $ignore, $entry));
			}
		}

		return $result;
	}

	/**
	 * Checks if the folder has any contents
	 *
	 * @param string $dir
	 * @return bool
	 */
	public static function isEmpty(string $dir): bool
	{
		return count(static::read($dir)) === 0;
	}

	/**
	 * Checks if the directory is readable
	 *
	 * @param string $dir
	 * @return bool
	 */
	public static function isReadable(string $dir): bool
	{
		return is_readable($dir);
	}

	/**
	 * Checks if the directory is writable
	 *
	 * @param string $dir
	 * @return bool
	 */
	public static function isWritable(string $dir): bool
	{
		return is_writable($dir);
	}

	/**
	 * Scans the directory and analyzes files,
	 * content, meta info and children. This is used
	 * in `Kirby\Cms\Page`, `Kirby\Cms\Site` and
	 * `Kirby\Cms\User` objects to fetch all
	 * relevant information.
	 *
	 * Don't use outside the Cms context.
	 *
	 * @internal
	 *
	 * @param string $dir
	 * @param string $contentExtension
	 * @param array|null $contentIgnore
	 * @param bool $multilang
	 * @return array
	 */
	public static function inventory(string $dir, string $contentExtension = 'txt', array $contentIgnore = null, bool $multilang = false): array
	{
		$dir = realpath($dir);

		$inventory = [
			'children' => [],
			'files'    => [],
			'template' => 'default',
		];

		if ($dir === false) {
			return $inventory;
		}

		$items = static::read($dir, $contentIgnore);

		// a temporary store for all content files
		$content = [];

		// sort all items naturally to avoid sorting issues later
		natsort($items);

		foreach ($items as $item) {

			// ignore all items with a leading dot
			if (in_array(substr($item, 0, 1), ['.', '_']) === true) {
				continue;
			}

			$root = $dir . '/' . $item;

			if (is_dir($root) === true) {

				// extract the slug and num of the directory
				if (preg_match('/^([0-9]+)' . static::$numSeparator . '(.*)$/', $item, $match)) {
					$num  = (int)$match[1];
					$slug = $match[2];
				} else {
					$num  = null;
					$slug = $item;
				}

				$inventory['children'][] = [
					'dirname' => $item,
					'model'   => null,
					'num'     => $num,
					'root'    => $root,
					'slug'    => $slug,
				];
			} else {
				$extension = pathinfo($item, PATHINFO_EXTENSION);

				switch ($extension) {
					case 'htm':
					case 'html':
					case 'php':
						// don't track those files
						break;
					case $contentExtension:
						$content[] = pathinfo($item, PATHINFO_FILENAME);
						break;
					default:
						$inventory['files'][$item] = [
							'filename'  => $item,
							'extension' => $extension,
							'root'      => $root,
						];
				}
			}
		}

		// remove the language codes from all content filenames
		if ($multilang === true) {
			foreach ($content as $key => $filename) {
				$content[$key] = pathinfo($filename, PATHINFO_FILENAME);
			}

			$content = array_unique($content);
		}

		$inventory = static::inventoryContent($inventory, $content);
		$inventory = static::inventoryModels($inventory, $contentExtension, $multilang);

		return $inventory;
	}

	/**
	 * Take all content files,
	 * remove those who are meta files and
	 * detect the main content file
	 *
	 * @param array $inventory
	 * @param array $content
	 * @return array
	 */
	protected static function inventoryContent(array $inventory, array $content): array
	{

		// filter meta files from the content file
		if (empty($content) === true) {
			$inventory['template'] = 'default';
			return $inventory;
		}

		foreach ($content as $contentName) {

			// could be a meta file. i.e. cover.jpg
			if (isset($inventory['files'][$contentName]) === true) {
				continue;
			}

			// it's most likely the template
			$inventory['template'] = $contentName;
		}

		return $inventory;
	}

	/**
	 * Go through all inventory children
	 * and inject a model for each
	 *
	 * @param array $inventory
	 * @param string $contentExtension
	 * @param bool $multilang
	 * @return array
	 */
	protected static function inventoryModels(array $inventory, string $contentExtension, bool $multilang = false): array
	{
		// inject models
		if (empty($inventory['children']) === false && empty(Page::$models) === false) {
			if ($multilang === true) {
				$contentExtension = App::instance()->defaultLanguage()->code() . '.' . $contentExtension;
			}

			foreach ($inventory['children'] as $key => $child) {
				foreach (Page::$models as $modelName => $modelClass) {
					if (file_exists($child['root'] . '/' . $modelName . '.' . $contentExtension) === true) {
						$inventory['children'][$key]['model'] = $modelName;
						break;
					}
				}
			}
		}

		return $inventory;
	}

	/**
	 * Create a (symbolic) link to a directory
	 *
	 * @param string $source
	 * @param string $link
	 * @return bool
	 */
	public static function link(string $source, string $link): bool
	{
		static::make(dirname($link), true);

		if (is_dir($link) === true) {
			return true;
		}

		if (is_dir($source) === false) {
			throw new Exception(sprintf('The directory "%s" does not exist and cannot be linked', $source));
		}

		try {
			return symlink($source, $link) === true;
		} catch (Throwable $e) {
			return false;
		}
	}

	/**
	 * Creates a new directory
	 *
	 * @param string $dir The path for the new directory
	 * @param bool $recursive Create all parent directories, which don't exist
	 * @return bool True: the dir has been created, false: creating failed
	 * @throws \Exception If a file with the provided path already exists or the parent directory is not writable
	 */
	public static function make(string $dir, bool $recursive = true): bool
	{
		if (empty($dir) === true) {
			return false;
		}

		if (is_dir($dir) === true) {
			return true;
		}

		if (is_file($dir) === true) {
			throw new Exception(sprintf('A file with the name "%s" already exists', $dir));
		}

		$parent = dirname($dir);

		if ($recursive === true) {
			if (is_dir($parent) === false) {
				static::make($parent, true);
			}
		}

		if (is_writable($parent) === false) {
			throw new Exception(sprintf('The directory "%s" cannot be created', $dir));
		}

		return mkdir($dir);
	}

	/**
	 * Recursively check when the dir and all
	 * subfolders have been modified for the last time.
	 *
	 * @param string $dir The path of the directory
	 * @param string $format
	 * @param string $handler
	 * @return int|string
	 */
	public static function modified(string $dir, string $format = null, string $handler = 'date')
	{
		$modified = filemtime($dir);
		$items    = static::read($dir);

		foreach ($items as $item) {
			if (is_file($dir . '/' . $item) === true) {
				$newModified = filemtime($dir . '/' . $item);
			} else {
				$newModified = static::modified($dir . '/' . $item);
			}

			$modified = ($newModified > $modified) ? $newModified : $modified;
		}

		return Str::date($modified, $format, $handler);
	}

	/**
	 * Moves a directory to a new location
	 *
	 * @param string $old The current path of the directory
	 * @param string $new The desired path where the dir should be moved to
	 * @return bool true: the directory has been moved, false: moving failed
	 */
	public static function move(string $old, string $new): bool
	{
		if ($old === $new) {
			return true;
		}

		if (is_dir($old) === false || is_dir($new) === true) {
			return false;
		}

		if (static::make(dirname($new), true) !== true) {
			throw new Exception('The parent directory cannot be created');
		}

		return rename($old, $new);
	}

	/**
	 * Returns a nicely formatted size of all the contents of the folder
	 *
	 * @param string $dir The path of the directory
	 * @param string|null|false $locale Locale for number formatting,
	 *                                  `null` for the current locale,
	 *                                  `false` to disable number formatting
	 * @return mixed
	 */
	public static function niceSize(string $dir, $locale = null)
	{
		return F::niceSize(static::size($dir), $locale);
	}

	/**
	 * Reads all files from a directory and returns them as an array.
	 * It skips unwanted invisible stuff.
	 *
	 * @param string $dir The path of directory
	 * @param array $ignore Optional array with filenames, which should be ignored
	 * @param bool $absolute If true, the full path for each item will be returned
	 * @return array An array of filenames
	 */
	public static function read(string $dir, array $ignore = null, bool $absolute = false): array
	{
		if (is_dir($dir) === false) {
			return [];
		}

		// create the ignore pattern
		$ignore ??= static::$ignore;
		$ignore   = array_merge($ignore, ['.', '..']);

		// scan for all files and dirs
		$result = array_values((array)array_diff(scandir($dir), $ignore));

		// add absolute paths
		if ($absolute === true) {
			$result = array_map(fn ($item) => $dir . '/' . $item, $result);
		}

		return $result;
	}

	/**
	 * Removes a folder including all containing files and folders
	 *
	 * @param string $dir
	 * @return bool
	 */
	public static function remove(string $dir): bool
	{
		$dir = realpath($dir);

		if (is_dir($dir) === false) {
			return true;
		}

		if (is_link($dir) === true) {
			return F::unlink($dir);
		}

		foreach (scandir($dir) as $childName) {
			if (in_array($childName, ['.', '..']) === true) {
				continue;
			}

			$child = $dir . '/' . $childName;

			if (is_dir($child) === true && is_link($child) === false) {
				static::remove($child);
			} else {
				F::unlink($child);
			}
		}

		return rmdir($dir);
	}

	/**
	 * Gets the size of the directory
	 *
	 * @param string $dir The path of the directory
	 * @param bool $recursive Include all subfolders and their files
	 * @return mixed
	 */
	public static function size(string $dir, bool $recursive = true)
	{
		if (is_dir($dir) === false) {
			return false;
		}

		// Get size for all direct files
		$size = F::size(static::files($dir, null, true));

		// if recursive, add sizes of all subdirectories
		if ($recursive === true) {
			foreach (static::dirs($dir, null, true) as $subdir) {
				$size += static::size($subdir);
			}
		}

		return $size;
	}

	/**
	 * Checks if the directory or any subdirectory has been
	 * modified after the given timestamp
	 *
	 * @param string $dir
	 * @param int $time
	 * @return bool
	 */
	public static function wasModifiedAfter(string $dir, int $time): bool
	{
		if (filemtime($dir) > $time) {
			return true;
		}

		$content = static::read($dir);

		foreach ($content as $item) {
			$subdir = $dir . '/' . $item;

			if (filemtime($subdir) > $time) {
				return true;
			}

			if (is_dir($subdir) === true && static::wasModifiedAfter($subdir, $time) === true) {
				return true;
			}
		}

		return false;
	}
}
