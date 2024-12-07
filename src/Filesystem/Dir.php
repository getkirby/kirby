<?php

namespace Kirby\Filesystem;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\Helpers;
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
	 */
	public static array $ignore = [
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

	public static string $numSeparator = '_';

	/**
	 * Copy the directory to a new destination
	 *
	 * @param array|false $ignore List of full paths to skip during copying
	 *                            or `false` to copy all files, including
	 *                            those listed in `Dir::$ignore`
	 */
	public static function copy(
		string $dir,
		string $target,
		bool $recursive = true,
		array|false $ignore = []
	): bool {
		if (is_dir($dir) === false) {
			throw new Exception('The directory "' . $dir . '" does not exist');
		}

		if (is_dir($target) === true) {
			throw new Exception('The target directory "' . $target . '" exists');
		}

		if (static::make($target) !== true) {
			throw new Exception('The target directory "' . $target . '" could not be created');
		}

		foreach (static::read($dir, $ignore === false ? [] : null) as $name) {
			$root = $dir . '/' . $name;

			if (
				is_array($ignore) === true &&
				in_array($root, $ignore) === true
			) {
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
	 */
	public static function dirs(
		string $dir,
		array|null $ignore = null,
		bool $absolute = false
	): array {
		$scan   = static::read($dir, $ignore, true);
		$result = array_values(array_filter($scan, 'is_dir'));

		if ($absolute !== true) {
			$result = array_map('basename', $result);
		}

		return $result;
	}

	/**
	 * Checks if the directory exists on disk
	 */
	public static function exists(string $dir): bool
	{
		return is_dir($dir) === true;
	}

	/**
	 * Get all files
	 */
	public static function files(
		string $dir,
		array|null $ignore = null,
		bool $absolute = false
	): array {
		$scan   = static::read($dir, $ignore, true);
		$result = array_values(array_filter($scan, 'is_file'));

		if ($absolute !== true) {
			$result = array_map('basename', $result);
		}

		return $result;
	}

	/**
	 * Read the directory and all subdirectories
	 *
	 * @todo Remove support for `$ignore = null` in a major release
	 * @param array|false|null $ignore Array of absolut file paths;
	 *                                 `false` to disable `Dir::$ignore` list
	 *                                 (passing null is deprecated)
	 */
	public static function index(
		string $dir,
		bool $recursive = false,
		array|false|null $ignore = [],
		string|null $path = null
	): array {
		$result = [];
		$dir    = realpath($dir);
		$items  = static::read($dir, $ignore === false ? [] : null);

		foreach ($items as $item) {
			$root = $dir . '/' . $item;

			if (
				is_array($ignore) === true &&
				in_array($root, $ignore) === true
			) {
				continue;
			}

			$entry    = $path !== null ? $path . '/' . $item : $item;
			$result[] = $entry;

			if ($recursive === true && is_dir($root) === true) {
				$result = [
					...$result,
					...static::index($root, true, $ignore, $entry)
				];
			}
		}

		return $result;
	}

	/**
	 * Checks if the folder has any contents
	 */
	public static function isEmpty(string $dir): bool
	{
		return count(static::read($dir)) === 0;
	}

	/**
	 * Checks if the directory is readable
	 */
	public static function isReadable(string $dir): bool
	{
		return is_readable($dir);
	}

	/**
	 * Checks if the directory is writable
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
	 */
	public static function inventory(
		string $dir,
		string $contentExtension = 'txt',
		array|null $contentIgnore = null,
		bool $multilang = false
	): array {
		$inventory = [
			'children' => [],
			'files'    => [],
			'template' => 'default',
		];

		$dir = realpath($dir);

		if ($dir === false) {
			return $inventory;
		}

		// a temporary store for all content files
		$content = [];

		// read and sort all items naturally to avoid sorting issues later
		$items = static::read($dir, $contentIgnore);
		natsort($items);

		// loop through all directory items and collect all relevant information
		foreach ($items as $item) {
			// ignore all items with a leading dot or underscore
			if (in_array(substr($item, 0, 1), ['.', '_']) === true) {
				continue;
			}

			$root = $dir . '/' . $item;

			// collect all directories as children
			if (is_dir($root) === true) {
				$inventory['children'][] = static::inventoryChild(
					$item,
					$root,
					$contentExtension,
					$multilang
				);
				continue;
			}

			$extension = pathinfo($item, PATHINFO_EXTENSION);

			// don't track files with these extensions
			if (in_array($extension, ['htm', 'html', 'php']) === true) {
				continue;
			}

			// collect all content files separately,
			// not as inventory entries
			if ($extension === $contentExtension) {
				$filename = pathinfo($item, PATHINFO_FILENAME);

				// remove the language codes from all content filenames
				if ($multilang === true) {
					$filename = pathinfo($filename, PATHINFO_FILENAME);
				}

				$content[] = $filename;
				continue;
			}

			// collect all other files
			$inventory['files'][$item] = [
				'filename'  => $item,
				'extension' => $extension,
				'root'      => $root,
			];
		}

		$content = array_unique($content);

		$inventory['template'] = static::inventoryTemplate(
			$content,
			$inventory['files']
		);

		return $inventory;
	}

	/**
	 * Collect information for a child for the inventory
	 */
	protected static function inventoryChild(
		string $item,
		string $root,
		string $contentExtension = 'txt',
		bool $multilang = false
	): array {
		// extract the slug and num of the directory
		if ($separator = strpos($item, static::$numSeparator)) {
			$num  = (int)substr($item, 0, $separator);
			$slug = substr($item, $separator + 1);
		}

		// determine the model
		if (empty(Page::$models) === false) {
			if ($multilang === true) {
				$code = App::instance()->defaultLanguage()->code();
				$contentExtension = $code . '.' . $contentExtension;
			}

			// look if a content file can be found
			// for any of the available models
			foreach (Page::$models as $modelName => $modelClass) {
				if (is_file($root . '/' . $modelName . '.' . $contentExtension) === true) {
					$model = $modelName;
					break;
				}
			}
		}

		return [
			'dirname' => $item,
			'model'   => $model ?? null,
			'num'     => $num ?? null,
			'root'    => $root,
			'slug'    => $slug ?? $item,
		];
	}

	/**
	 * Determines the main template for the inventory
	 * from all collected content files, ignore file meta files
	 */
	protected static function inventoryTemplate(
		array $content,
		array $files,
	): string {
		foreach ($content as $name) {
			// is a meta file corresponding to an actual file, i.e. cover.jpg
			if (isset($files[$name]) === true) {
				continue;
			}

			// it's most likely the template
			// (will overwrite and use the last match for historic reasons)
			$template = $name;
		}

		return $template ?? 'default';
	}

	/**
	 * Create a (symbolic) link to a directory
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
		} catch (Throwable) {
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

		if ($recursive === true && is_dir($parent) === false) {
			static::make($parent, true);
		}

		if (is_writable($parent) === false) {
			throw new Exception(sprintf('The directory "%s" cannot be created', $dir));
		}

		return Helpers::handleErrors(
			fn (): bool => mkdir($dir),
			// if the dir was already created (race condition),
			fn (int $errno, string $errstr): bool => Str::endsWith($errstr, 'File exists'),
			// consider it a success
			true
		);
	}

	/**
	 * Recursively check when the dir and all
	 * subfolders have been modified for the last time.
	 *
	 * @param string $dir The path of the directory
	 * @param 'date'|'intl'|'strftime'|null $handler Custom date handler or `null`
	 *                                               for the globally configured one
	 */
	public static function modified(
		string $dir,
		string|null $format = null,
		string|null $handler = null
	): int|string {
		$modified = filemtime($dir);
		$items    = static::read($dir);

		foreach ($items as $item) {
			$newModified = match (is_file($dir . '/' . $item)) {
				true  => filemtime($dir . '/' . $item),
				false => static::modified($dir . '/' . $item)
			};
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
	 * @param string|false|null $locale Locale for number formatting,
	 *                                  `null` for the current locale,
	 *                                  `false` to disable number formatting
	 */
	public static function niceSize(
		string $dir,
		string|false|null $locale = null
	): string {
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
	public static function read(
		string $dir,
		array|null $ignore = null,
		bool $absolute = false
	): array {
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
	 */
	public static function size(string $dir, bool $recursive = true): int|false
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

			if (
				is_dir($subdir) === true &&
				static::wasModifiedAfter($subdir, $time) === true
			) {
				return true;
			}
		}

		return false;
	}
}
