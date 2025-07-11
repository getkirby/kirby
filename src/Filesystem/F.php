<?php

namespace Kirby\Filesystem;

use Exception;
use IntlDateFormatter;
use Kirby\Cms\Helpers;
use Kirby\Http\Response;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;
use ZipArchive;

/**
 * The `F` class provides methods for
 * dealing with files on the file system
 * level, like creating, reading,
 * deleting, copying or validatings files.
 *
 * @package   Kirby Filesystem
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class F
{
	public static array $types = [
		'archive' => [
			'gz',
			'gzip',
			'tar',
			'tgz',
			'zip',
		],
		'audio' => [
			'aif',
			'aiff',
			'm4a',
			'midi',
			'mp3',
			'wav',
		],
		'code' => [
			'css',
			'js',
			'json',
			'java',
			'htm',
			'html',
			'php',
			'rb',
			'py',
			'scss',
			'xml',
			'yaml',
			'yml',
		],
		'document' => [
			'csv',
			'doc',
			'docx',
			'dotx',
			'indd',
			'md',
			'mdown',
			'pdf',
			'ppt',
			'pptx',
			'rtf',
			'txt',
			'xl',
			'xls',
			'xlsx',
			'xltx',
		],
		'image' => [
			'ai',
			'avif',
			'bmp',
			'gif',
			'eps',
			'ico',
			'j2k',
			'jp2',
			'jpeg',
			'jpg',
			'jpe',
			'png',
			'ps',
			'psd',
			'svg',
			'tif',
			'tiff',
			'webp'
		],
		'video' => [
			'avi',
			'flv',
			'm4v',
			'mov',
			'movie',
			'mpe',
			'mpg',
			'mp4',
			'ogg',
			'ogv',
			'swf',
			'webm',
		],
	];

	public static array $units = [
		'B',
		'KB',
		'MB',
		'GB',
		'TB',
		'PB',
		'EB',
		'ZB',
		'YB'
	];

	/**
	 * Appends new content to an existing file
	 *
	 * @param string $file The path for the file
	 * @param mixed $content Either a string or an array. Arrays will be converted to JSON.
	 */
	public static function append(string $file, $content): bool
	{
		return static::write($file, $content, true);
	}

	/**
	 * Returns the file content as base64 encoded string
	 *
	 * @param string $file The path for the file
	 */
	public static function base64(string $file): string
	{
		return base64_encode(static::read($file));
	}

	/**
	 * Copy a file to a new location.
	 */
	public static function copy(
		string $source,
		string $target,
		bool $force = false
	): bool {
		if (file_exists($source) === false) {
			return false;
		}

		if (file_exists($target) === true && $force === false) {
			return false;
		}

		$directory = dirname($target);

		// create the parent directory if it does not exist
		if (is_dir($directory) === false) {
			Dir::make($directory, true);
		}

		return copy($source, $target);
	}

	/**
	 * Just an alternative for dirname() to stay consistent
	 *
	 * ```php
	 * $dirname = F::dirname('/var/www/test.txt');
	 * // dirname is /var/www
	 * ```
	 *
	 * @param string $file The path
	 */
	public static function dirname(string $file): string
	{
		return dirname($file);
	}

	/**
	 * Checks if the file exists on disk
	 */
	public static function exists(string $file, string|null $in = null): bool
	{
		try {
			static::realpath($file, $in);
			return true;
		} catch (Exception) {
			return false;
		}
	}

	/**
	 * Gets the extension of a file
	 *
	 * @param string|null $file The filename or path
	 * @param string|null $extension Set an optional extension to overwrite the current one
	 */
	public static function extension(
		string|null $file = null,
		string|null $extension = null
	): string {
		// overwrite the current extension
		if ($extension !== null) {
			return static::name($file) . '.' . $extension;
		}

		// return the current extension
		return Str::lower(pathinfo($file, PATHINFO_EXTENSION));
	}

	/**
	 * Converts a file extension to a mime type
	 */
	public static function extensionToMime(string $extension): string|null
	{
		return Mime::fromExtension($extension);
	}

	/**
	 * Returns the file type for a passed extension
	 */
	public static function extensionToType(string $extension): string|false
	{
		foreach (static::$types as $type => $extensions) {
			if (in_array($extension, $extensions, true) === true) {
				return $type;
			}
		}

		return false;
	}

	/**
	 * Returns all extensions for a certain file type
	 */
	public static function extensions(string|null $type = null): array
	{
		if ($type === null) {
			return array_keys(Mime::types());
		}

		return static::$types[$type] ?? [];
	}

	/**
	 * Extracts the filename from a file path
	 *
	 * ```php
	 * $filename = F::filename('/var/www/test.txt');
	 * // filename is test.txt
	 * ```
	 *
	 * @param string $name The path
	 */
	public static function filename(string $name): string
	{
		return pathinfo($name, PATHINFO_BASENAME);
	}

	/**
	 * Invalidate opcode cache for file.
	 *
	 * @param string $file The path of the file
	 */
	public static function invalidateOpcodeCache(string $file): bool
	{
		if (
			function_exists('opcache_invalidate') &&
			strlen(ini_get('opcache.restrict_api')) === 0
		) {
			return opcache_invalidate($file, true);
		}

		return false;
	}

	/**
	 * Checks if a file is of a certain type
	 *
	 * @param string $file Full path to the file
	 * @param string $value An extension or mime type
	 */
	public static function is(string $file, string $value): bool
	{
		// check for the extension
		if (in_array($value, static::extensions(), true) === true) {
			return static::extension($file) === $value;
		}

		// check for the mime type
		if (str_contains($value, '/') === true) {
			return static::mime($file) === $value;
		}

		return false;
	}

	/**
	 * Checks if the file is readable
	 */
	public static function isReadable(string $file): bool
	{
		return is_readable($file);
	}

	/**
	 * Checks if the file is writable
	 */
	public static function isWritable(string $file): bool
	{
		if (file_exists($file) === false) {
			return is_writable(dirname($file));
		}

		return is_writable($file);
	}

	/**
	 * Create a (symbolic) link to a file
	 */
	public static function link(
		string $source,
		string $link,
		string $method = 'link'
	): bool {
		Dir::make(dirname($link), true);

		if (is_file($link) === true) {
			return true;
		}

		if (is_file($source) === false) {
			throw new Exception(sprintf('The file "%s" does not exist and cannot be linked', $source));
		}

		try {
			return $method($source, $link) === true;
		} catch (Throwable) {
			return false;
		}
	}

	/**
	 * Loads a file and returns the result or `false` if the
	 * file to load does not exist
	 *
	 * @param array $data Optional array of variables to extract in the variable scope
	 */
	public static function load(
		string $file,
		mixed $fallback = null,
		array $data = [],
		bool $allowOutput = true
	) {
		if (is_file($file) === false) {
			return $fallback;
		}

		// we use the loadIsolated() method here to prevent the included
		// file from overwriting our $fallback in this variable scope; see
		// https://www.php.net/manual/en/function.include.php#example-124
		$callback = fn () => static::loadIsolated($file, $data);

		// if the loaded file should not produce any output,
		// call the loaidIsolated method from the Response class
		// which checks for unintended ouput and throws an error if detected
		$result = match ($allowOutput) {
			true  => $callback(),
			false => Response::guardAgainstOutput($callback),
		};

		if (
			$fallback !== null &&
			gettype($result) !== gettype($fallback)
		) {
			return $fallback;
		}

		return $result;
	}

	/**
	 * A super simple class autoloader
	 * @since 3.7.0
	 */
	public static function loadClasses(
		array $classmap,
		string|null $base = null
	): void {
		// convert all classnames to lowercase
		$classmap = array_change_key_case($classmap);

		spl_autoload_register(
			fn ($class) => Response::guardAgainstOutput(function () use ($class, $classmap, $base) {
				$class = strtolower($class);

				if (isset($classmap[$class]) === false) {
					return false;
				}

				if ($base) {
					include $base . '/' . $classmap[$class];
				} else {
					include $classmap[$class];
				}
			})
		);
	}

	/**
	 * Loads a file with as little as possible in the variable scope
	 *
	 * @param array $data Optional array of variables to extract in the variable scope
	 */
	protected static function loadIsolated(string $file, array $data = [])
	{
		// extract the $data variables in this scope to be accessed by the included file;
		// protect $file against being overwritten by a $data variable
		$___file___ = $file;
		extract($data);

		return include $___file___;
	}

	/**
	 * Loads a file using `include_once()` and
	 * returns whether loading was successful
	 */
	public static function loadOnce(
		string $file,
		bool $allowOutput = true
	): bool {
		if (is_file($file) === false) {
			return false;
		}

		$callback = fn () => include_once $file;

		if ($allowOutput === false) {
			Response::guardAgainstOutput($callback);
		} else {
			$callback();
		}

		return true;
	}

	/**
	 * Returns the mime type of a file
	 */
	public static function mime(string $file): string|null
	{
		return Mime::type($file);
	}

	/**
	 * Converts a mime type to a file extension
	 */
	public static function mimeToExtension(
		string|null $mime = null
	): string|false {
		return Mime::toExtension($mime);
	}

	/**
	 * Returns the type for a given mime
	 */
	public static function mimeToType(string $mime): string|false
	{
		return static::extensionToType(Mime::toExtension($mime));
	}

	/**
	 * Get the file's last modification time.
	 *
	 * @param 'date'|'intl'|'strftime'|null $handler Custom date handler or `null`
	 *                                               for the globally configured one
	 */
	public static function modified(
		string $file,
		string|IntlDateFormatter|null $format = null,
		string|null $handler = null
	): string|int|false {
		if (file_exists($file) !== true) {
			return false;
		}

		$modified = filemtime($file);

		return Str::date($modified, $format, $handler);
	}

	/**
	 * Moves a file to a new location
	 *
	 * @param string $oldRoot The current path for the file
	 * @param string $newRoot The path to the new location
	 * @param bool $force Force move if the target file exists
	 */
	public static function move(
		string $oldRoot,
		string $newRoot,
		bool $force = false
	): bool {
		// check if the file exists
		if (file_exists($oldRoot) === false) {
			return false;
		}

		if (file_exists($newRoot) === true) {
			if ($force === false) {
				return false;
			}

			// delete the existing file
			static::remove($newRoot);
		}

		$directory = dirname($newRoot);

		// create the parent directory if it does not exist
		if (is_dir($directory) === false) {
			Dir::make($directory, true);
		}

		// atomically moving the file will only work if
		// source and target are on the same filesystem
		if (stat($oldRoot)['dev'] === stat($directory)['dev']) {
			// same filesystem, we can move the file
			return rename($oldRoot, $newRoot) === true;
		}

		// @codeCoverageIgnoreStart
		// not the same filesystem; we need to copy
		// the file and unlink the source afterwards
		if (copy($oldRoot, $newRoot) === true) {
			return unlink($oldRoot) === true;
		}

		// copying failed, ensure the new root isn't there
		// (e.g. if the file could be created but there's no
		// more remaining disk space to write its contents)
		static::remove($newRoot);
		return false;
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Extracts the name from a file path or filename without extension
	 *
	 * @param string $name The path or filename
	 */
	public static function name(string $name): string
	{
		return pathinfo($name, PATHINFO_FILENAME);
	}

	/**
	 * Converts an integer size into a human readable format
	 *
	 * @param int|string|array $size The file size, a file path or array of paths
	 * @param string|false|null $locale Locale for number formatting,
	 *                                  `null` for the current locale,
	 *                                  `false` to disable number formatting
	 */
	public static function niceSize(
		int|string|array $size,
		string|false|null $locale = null
	): string {
		// file mode
		if (is_string($size) === true || is_array($size) === true) {
			$size = static::size($size);
		}

		// make sure it's an int
		$size = (int)$size;

		// avoid errors for invalid sizes
		if ($size <= 0) {
			return '0 KB';
		}

		// the math magic
		$size = round($size / 1024 ** ($unit = floor(log($size, 1024))), 2);

		// format the number if requested
		if ($locale !== false) {
			$size = I18n::formatNumber($size, $locale);
		}

		return $size . ' ' . static::$units[$unit];
	}

	/**
	 * Reads the content of a file or requests the
	 * contents of a remote HTTP or HTTPS URL
	 *
	 * @param string $file The path for the file or an absolute URL
	 */
	public static function read(string $file): string|false
	{
		if (str_contains($file, '://') === true) {
			return false;
		}

		// exit early on empty paths that would trigger a PHP `ValueError`
		if ($file === '') {
			return false;
		}

		// to increase performance, directly try to load the file without checking
		// if it exists; fall back to a `false` return value if it doesn't exist
		// while letting other warnings through
		return Helpers::handleErrors(
			fn (): string|false => file_get_contents($file),
			fn (int $errno, string $errstr): bool => str_contains($errstr, 'No such file'),
			false
		);
	}

	/**
	 * Changes the name of the file without
	 * touching the extension
	 *
	 * @param bool $overwrite Force overwrite existing files
	 */
	public static function rename(
		string $file,
		string $newName,
		bool $overwrite = false
	): string|false {
		// create the new name
		$name = static::safeName(basename($newName));

		// overwrite the root
		$newRoot = rtrim(dirname($file) . '/' . $name . '.' . F::extension($file), '.');

		// nothing has changed
		if ($newRoot === $file) {
			return $newRoot;
		}

		if (F::move($file, $newRoot, $overwrite) !== true) {
			return false;
		}

		return $newRoot;
	}

	/**
	 * Returns the absolute path to the file if the file can be found.
	 */
	public static function realpath(
		string $file,
		string|null $in = null
	): string {
		$realpath = realpath($file);

		if ($realpath === false || is_file($realpath) === false) {
			throw new Exception(sprintf('The file does not exist at the given path: "%s"', $file));
		}

		if ($in !== null) {
			$parent = realpath($in);

			if ($parent === false || is_dir($parent) === false) {
				throw new Exception(sprintf('The parent directory does not exist: "%s"', $in));
			}

			if (str_starts_with($realpath, $parent) === false) {
				throw new Exception('The file is not within the parent directory');
			}
		}

		return $realpath;
	}

	/**
	 * Returns the relative path of the file
	 * starting after $in
	 *
	 * @SuppressWarnings(PHPMD.CountInLoopExpression)
	 */
	public static function relativepath(
		string $file,
		string|null $in = null
	): string {
		if (empty($in) === true) {
			return basename($file);
		}

		// windows
		$file = str_replace('\\', '/', $file);
		$in   = str_replace('\\', '/', $in);

		// trim trailing slashes
		$file = rtrim($file, '/');
		$in   = rtrim($in, '/');

		if (Str::contains($file, $in . '/') === false) {
			// make the paths relative by stripping what they have
			// in common and adding `../` tokens at the start
			$fileParts = explode('/', $file);
			$inParts   = explode('/', $in);

			while (
				count($fileParts) &&
				count($inParts) &&
				($fileParts[0] === $inParts[0])
			) {
				array_shift($fileParts);
				array_shift($inParts);
			}

			return str_repeat('../', count($inParts)) . implode('/', $fileParts);
		}

		return '/' . Str::after($file, $in . '/');
	}

	/**
	 * Deletes a file
	 *
	 * ```php
	 * $remove = F::remove('test.txt');
	 * if ($remove) echo 'The file has been removed';
	 * ```
	 *
	 * @param string $file The path for the file
	 */
	public static function remove(string $file): bool
	{
		if (str_contains($file, '*') === true) {
			foreach (glob($file) as $f) {
				static::remove($f);
			}

			return true;
		}

		$file = realpath($file);

		if (is_string($file) === false) {
			return true;
		}

		return static::unlink($file);
	}

	/**
	 * Sanitize a file's full name (filename and extension)
	 * to strip unwanted special characters
	 *
	 * ```php
	 * $safe = f::safeName('über genius.txt');
	 * // safe will be ueber-genius.txt
	 * ```
	 *
	 * @param string $string The file name
	 */
	public static function safeName(string $string): string
	{
		$basename  = static::safeBasename($string);
		$extension =  static::safeExtension($string);

		if (empty($extension) === false) {
			$extension = '.' . $extension;
		}

		return $basename . $extension;
	}

	/**
	 * Sanitize a file's name (without extension)
	 * @since 4.0.0
	 */
	public static function safeBasename(
		string $string,
		bool $extract = true
	): string {
		// extract only the name part from whole filename string
		if ($extract === true) {
			$string = static::name($string);
		}

		return Str::slug($string, '-', 'a-z0-9@._-');
	}

	/**
	 * Sanitize a file's extension
	 * @since 4.0.0
	 */
	public static function safeExtension(
		string $string,
		bool $extract = true
	): string {
		// extract only the extension part from whole filename string
		if ($extract === true) {
			$string = static::extension($string);
		}

		return Str::slug($string);
	}

	/**
	 * Tries to find similar or the same file by
	 * building a glob based on the path
	 */
	public static function similar(string $path, string $pattern = '*'): array
	{
		$dir       = dirname($path);
		$name      = static::name($path);
		$extension = static::extension($path);
		$glob      = $dir . '/' . $name . $pattern . '.' . $extension;
		return glob($glob);
	}

	/**
	 * Returns the size of a file or an array of files.
	 *
	 * @param string|array $file file path or array of paths
	 */
	public static function size(string|array $file): int
	{
		if (is_array($file) === true) {
			return array_reduce(
				$file,
				fn ($total, $file) => $total + F::size($file),
				0
			);
		}

		if ($size = @filesize($file)) {
			return $size;
		}

		return 0;
	}

	/**
	 * Categorize the file
	 *
	 * @param string $file Either the file path or extension
	 */
	public static function type(string $file): string|null
	{
		$length    = strlen($file);
		$extension = match ($length >= 2 && $length <= 4) {
			// use the file name as extension
			true  => $file,
			// get the extension from the filename
			false => pathinfo($file, PATHINFO_EXTENSION)
		};

		if (empty($extension) === true || $extension === 'tmp') {
			// detect the mime type first to get the most reliable extension
			$mime      = static::mime($file);
			$extension = static::mimeToExtension($mime);
		}

		// sanitize extension
		$extension = strtolower($extension);

		foreach (static::$types as $type => $extensions) {
			if (in_array($extension, $extensions, true) === true) {
				return $type;
			}
		}

		return null;
	}

	/**
	 * Returns all extensions of a given file type
	 * or `null` if the file type is unknown
	 */
	public static function typeToExtensions(string $type): array|null
	{
		return static::$types[$type] ?? null;
	}

	/**
	 * Ensures that a file or link is deleted (with race condition handling)
	 * @since 3.7.4
	 */
	public static function unlink(string $file): bool
	{
		return Helpers::handleErrors(
			fn (): bool => unlink($file),
			// if the file or link was already deleted (race condition),
			fn (int $errno, string $errstr): bool => Str::endsWith($errstr, 'No such file or directory'),
			// consider it a success
			true
		);
	}

	/**
	 * Unzips a zip file
	 */
	public static function unzip(string $file, string $to): bool
	{
		if (class_exists('ZipArchive') === false) {
			throw new Exception('The ZipArchive class is not available');
		}

		$zip = new ZipArchive();

		if ($zip->open($file) === true) {
			$zip->extractTo($to);
			$zip->close();
			return true;
		}

		return false;
	}

	/**
	 * Returns the file as data uri
	 *
	 * @param string $file The path for the file
	 */
	public static function uri(string $file): string|false
	{
		if ($mime = static::mime($file)) {
			return 'data:' . $mime . ';base64,' . static::base64($file);
		}

		return false;
	}

	/**
	 * Creates a new file
	 *
	 * @param string $file The path for the new file
	 * @param mixed $content Either a string, an object or an array. Arrays and objects will be serialized.
	 * @param bool $append true: append the content to an existing file if available. false: overwrite.
	 */
	public static function write(
		string $file,
		$content,
		bool $append = false
	): bool {
		if (is_array($content) === true || is_object($content) === true) {
			$content = serialize($content);
		}

		$mode = $append === true ? FILE_APPEND | LOCK_EX : LOCK_EX;

		// if the parent directory does not exist, create it
		if (is_dir(dirname($file)) === false) {
			if (Dir::make(dirname($file)) === false) {
				return false;
			}
		}

		if (static::isWritable($file) === false) {
			throw new Exception('The file "' . $file . '" is not writable');
		}

		return file_put_contents($file, $content, $mode) !== false;
	}
}
