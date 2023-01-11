<?php

namespace Kirby\Filesystem;

use Kirby\Toolkit\Str;
use SimpleXMLElement;

/**
 * The `Mime` class provides method
 * for MIME type detection or guessing
 * from different criteria like
 * extensions etc.
 *
 * @package   Kirby Filesystem
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Mime
{
	/**
	 * Extension to MIME type map
	 *
	 * @var array
	 */
	public static $types = [
		'ai'    => 'application/postscript',
		'aif'   => 'audio/x-aiff',
		'aifc'  => 'audio/x-aiff',
		'aiff'  => 'audio/x-aiff',
		'avi'   => 'video/x-msvideo',
		'avif'   => 'image/avif',
		'bmp'   => 'image/bmp',
		'css'   => 'text/css',
		'csv'   => ['text/csv', 'text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream'],
		'doc'   => 'application/msword',
		'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'dotx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
		'dvi'   => 'application/x-dvi',
		'eml'   => 'message/rfc822',
		'eps'   => 'application/postscript',
		'exe'   => ['application/octet-stream', 'application/x-msdownload'],
		'gif'   => 'image/gif',
		'gtar'  => 'application/x-gtar',
		'gz'    => 'application/x-gzip',
		'htm'   => 'text/html',
		'html'  => 'text/html',
		'ico'   => 'image/x-icon',
		'ics'   => 'text/calendar',
		'js'    => ['application/javascript', 'application/x-javascript'],
		'json'  => ['application/json', 'text/json'],
		'j2k'   => ['image/jp2'],
		'jp2'   => ['image/jp2'],
		'jpg'   => ['image/jpeg', 'image/pjpeg'],
		'jpeg'  => ['image/jpeg', 'image/pjpeg'],
		'jpe'   => ['image/jpeg', 'image/pjpeg'],
		'log'   => ['text/plain', 'text/x-log'],
		'm4a'   => 'audio/mp4',
		'm4v'   => 'video/mp4',
		'mid'   => 'audio/midi',
		'midi'  => 'audio/midi',
		'mif'   => 'application/vnd.mif',
		'mjs'   => 'text/javascript',
		'mov'   => 'video/quicktime',
		'movie' => 'video/x-sgi-movie',
		'mp2'   => 'audio/mpeg',
		'mp3'   => ['audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'],
		'mp4'   => 'video/mp4',
		'mpe'   => 'video/mpeg',
		'mpeg'  => 'video/mpeg',
		'mpg'   => 'video/mpeg',
		'mpga'  => 'audio/mpeg',
		'odc'   => 'application/vnd.oasis.opendocument.chart',
		'odp'   => 'application/vnd.oasis.opendocument.presentation',
		'odt'   => 'application/vnd.oasis.opendocument.text',
		'pdf'   => ['application/pdf', 'application/x-download'],
		'php'   => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
		'php3'  => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
		'phps'  => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
		'phtml' => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
		'png'   => 'image/png',
		'ppt'   => ['application/powerpoint', 'application/vnd.ms-powerpoint'],
		'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'potx'  => 'application/vnd.openxmlformats-officedocument.presentationml.template',
		'ps'    => 'application/postscript',
		'psd'   => 'application/x-photoshop',
		'qt'    => 'video/quicktime',
		'rss'   => 'application/rss+xml',
		'rtf'   => 'text/rtf',
		'rtx'   => 'text/richtext',
		'shtml' => 'text/html',
		'svg'   => 'image/svg+xml',
		'swf'   => 'application/x-shockwave-flash',
		'tar'   => 'application/x-tar',
		'text'  => 'text/plain',
		'txt'   => 'text/plain',
		'tgz'   => ['application/x-tar', 'application/x-gzip-compressed'],
		'tif'   => 'image/tiff',
		'tiff'  => 'image/tiff',
		'wav'   => 'audio/x-wav',
		'wbxml' => 'application/wbxml',
		'webm'  => 'video/webm',
		'webp'  => 'image/webp',
		'word'  => ['application/msword', 'application/octet-stream'],
		'xhtml' => 'application/xhtml+xml',
		'xht'   => 'application/xhtml+xml',
		'xml'   => 'text/xml',
		'xl'    => 'application/excel',
		'xls'   => ['application/excel', 'application/vnd.ms-excel', 'application/msexcel'],
		'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xltx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
		'xsl'   => 'text/xml',
		'yaml'  => ['application/yaml', 'text/yaml'],
		'yml'   => ['application/yaml', 'text/yaml'],
		'zip'   => ['application/x-zip', 'application/zip', 'application/x-zip-compressed'],
	];

	/**
	 * Fixes an invalid MIME type guess for the given file
	 *
	 * @param string $file
	 * @param string $mime
	 * @param string $extension
	 * @return string|null
	 */
	public static function fix(string $file, string $mime = null, string $extension = null)
	{
		// fixing map
		$map = [
			'text/html' => [
				'svg' => ['Kirby\Filesystem\Mime', 'fromSvg'],
			],
			'text/plain' => [
				'css'  => 'text/css',
				'json' => 'application/json',
				'mjs' => 'text/javascript',
				'svg'  => ['Kirby\Filesystem\Mime', 'fromSvg'],
			],
			'text/x-asm' => [
				'css' => 'text/css'
			],
			'text/x-java' => [
				'mjs' => 'text/javascript',
			],
			'image/svg' => [
				'svg' => 'image/svg+xml'
			],
			'application/octet-stream' => [
				'mjs' => 'text/javascript'
			]
		];

		if ($mode = ($map[$mime][$extension] ?? null)) {
			if (is_callable($mode) === true) {
				return $mode($file, $mime, $extension);
			}

			if (is_string($mode) === true) {
				return $mode;
			}
		}

		return $mime;
	}

	/**
	 * Guesses a MIME type by extension
	 *
	 * @param string $extension
	 * @return string|null
	 */
	public static function fromExtension(string $extension): string|null
	{
		$mime = static::$types[$extension] ?? null;
		return is_array($mime) === true ? array_shift($mime) : $mime;
	}

	/**
	 * Returns the MIME type of a file
	 *
	 * @param string $file
	 * @return string|false
	 */
	public static function fromFileInfo(string $file)
	{
		if (function_exists('finfo_file') === true && file_exists($file) === true) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime  = finfo_file($finfo, $file);
			finfo_close($finfo);
			return $mime;
		}

		return false;
	}

	/**
	 * Returns the MIME type of a file
	 *
	 * @param string $file
	 * @return string|false
	 */
	public static function fromMimeContentType(string $file)
	{
		if (function_exists('mime_content_type') === true && file_exists($file) === true) {
			return mime_content_type($file);
		}

		return false;
	}

	/**
	 * Tries to detect a valid SVG and returns the MIME type accordingly
	 *
	 * @param string $file
	 * @return string|false
	 */
	public static function fromSvg(string $file)
	{
		if (file_exists($file) === true) {
			libxml_use_internal_errors(true);

			$svg = new SimpleXMLElement(file_get_contents($file));

			if ($svg !== false && $svg->getName() === 'svg') {
				return 'image/svg+xml';
			}
		}

		return false;
	}

	/**
	 * Tests if a given MIME type is matched by an `Accept` header
	 * pattern; returns true if the MIME type is contained at all
	 *
	 * @param string $mime
	 * @param string $pattern
	 * @return bool
	 */
	public static function isAccepted(string $mime, string $pattern): bool
	{
		$accepted = Str::accepted($pattern);

		foreach ($accepted as $m) {
			if (static::matches($mime, $m['value']) === true) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Tests if a MIME wildcard pattern from an `Accept` header
	 * matches a given type
	 * @since 3.3.0
	 *
	 * @param string $test
	 * @param string $wildcard
	 * @return bool
	 */
	public static function matches(string $test, string $wildcard): bool
	{
		return fnmatch($wildcard, $test, FNM_PATHNAME) === true;
	}

	/**
	 * Returns the extension for a given MIME type
	 *
	 * @param string|null $mime
	 * @return string|false
	 */
	public static function toExtension(string $mime = null)
	{
		foreach (static::$types as $key => $value) {
			if (is_array($value) === true && in_array($mime, $value) === true) {
				return $key;
			}

			if ($value === $mime) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Returns all available extensions for a given MIME type
	 *
	 * @param string|null $mime
	 * @return array
	 */
	public static function toExtensions(string $mime = null): array
	{
		$extensions = [];

		foreach (static::$types as $key => $value) {
			if (is_array($value) === true && in_array($mime, $value) === true) {
				$extensions[] = $key;
				continue;
			}

			if ($value === $mime) {
				$extensions[] = $key;
			}
		}

		return $extensions;
	}

	/**
	 * Returns the MIME type of a file
	 */
	public static function type(string $file, string|null $extension = null): string|null
	{
		// use the standard finfo extension
		$mime = static::fromFileInfo($file);

		// use the mime_content_type function
		if ($mime === false) {
			$mime = static::fromMimeContentType($file);
		}

		// get the extension or extract it from the filename
		$extension ??= F::extension($file);

		// try to guess the mime type at least
		if ($mime === false) {
			$mime = static::fromExtension($extension);
		}

		// fix broken mime detection
		return static::fix($file, $mime, $extension);
	}

	/**
	 * Returns all detectable MIME types
	 *
	 * @return array
	 */
	public static function types(): array
	{
		return static::$types;
	}
}
