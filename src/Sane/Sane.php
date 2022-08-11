<?php

namespace Kirby\Sane;

use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;

/**
 * The `Sane` class validates that files
 * don't contain potentially harmful contents.
 * The class comes with handlers for `svg`, `svgz` and `xml`
 * files for now, but can be extended and customized.
 * @since 3.5.4
 *
 * @package   Kirby Sane
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Sane
{
	/**
	 * Handler Type Aliases
	 *
	 * @var array
	 */
	public static $aliases = [
		'application/xml' => 'xml',
		'image/svg'       => 'svg',
		'image/svg+xml'   => 'svg',
		'text/html'       => 'html',
		'text/xml'        => 'xml',
	];

	/**
	 * All registered handlers
	 *
	 * @var array
	 */
	public static $handlers = [
		'html' => 'Kirby\Sane\Html',
		'svg'  => 'Kirby\Sane\Svg',
		'svgz' => 'Kirby\Sane\Svgz',
		'xml'  => 'Kirby\Sane\Xml',
	];

	/**
	 * Handler getter
	 *
	 * @param string $type
	 * @param bool $lazy If set to `true`, `null` is returned for undefined handlers
	 * @return \Kirby\Sane\Handler|null
	 *
	 * @throws \Kirby\Exception\NotFoundException If no handler was found and `$lazy` was set to `false`
	 */
	public static function handler(string $type, bool $lazy = false)
	{
		// normalize the type
		$type = mb_strtolower($type);

		// find a handler or alias
		$handler = static::$handlers[$type] ??
				   static::$handlers[static::$aliases[$type] ?? null] ??
				   null;

		if (empty($handler) === false && class_exists($handler) === true) {
			return new $handler();
		}

		if ($lazy === true) {
			return null;
		}

		throw new NotFoundException('Missing handler for type: "' . $type . '"');
	}

	/**
	 * Sanitizes the given string with the specified handler
	 * @since 3.6.0
	 *
	 * @param string $string
	 * @param string $type
	 * @return string
	 */
	public static function sanitize(string $string, string $type): string
	{
		return static::handler($type)->sanitize($string);
	}

	/**
	 * Sanitizes the contents of a file by overwriting
	 * the file with the sanitized version;
	 * the sane handlers are automatically chosen by
	 * the extension and MIME type if not specified
	 * @since 3.6.0
	 *
	 * @param string $file
	 * @param string|bool $typeLazy Explicit handler type string,
	 *                              `true` for lazy autodetection or
	 *                              `false` for normal autodetection
	 * @return void
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
	 * @throws \Kirby\Exception\LogicException If more than one handler applies
	 * @throws \Kirby\Exception\NotFoundException If the handler was not found
	 * @throws \Kirby\Exception\Exception On other errors
	 */
	public static function sanitizeFile(string $file, $typeLazy = false): void
	{
		if (is_string($typeLazy) === true) {
			static::handler($typeLazy)->sanitizeFile($file);
			return;
		}

		// try to find exactly one matching handler
		$handlers = static::handlersForFile($file, $typeLazy === true);
		switch (count($handlers)) {
			case 0:
				// lazy autodetection didn't find a handler
				break;
			case 1:
				$handlers[0]->sanitizeFile($file);
				break;
			default:
				// more than one matching handler;
				// sanitizing with all handlers will not leave much in the output
				$handlerNames = array_map('get_class', $handlers);
				throw new LogicException(
					'Cannot sanitize file as more than one handler applies: ' .
					implode(', ', $handlerNames)
				);
		}
	}

	/**
	 * Validates file contents with the specified handler
	 *
	 * @param string $string
	 * @param string $type
	 * @return void
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
	 * @throws \Kirby\Exception\NotFoundException If the handler was not found
	 * @throws \Kirby\Exception\Exception On other errors
	 */
	public static function validate(string $string, string $type): void
	{
		static::handler($type)->validate($string);
	}

	/**
	 * Validates the contents of a file;
	 * the sane handlers are automatically chosen by
	 * the extension and MIME type if not specified
	 *
	 * @param string $file
	 * @param string|bool $typeLazy Explicit handler type string,
	 *                              `true` for lazy autodetection or
	 *                              `false` for normal autodetection
	 * @return void
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
	 * @throws \Kirby\Exception\NotFoundException If the handler was not found
	 * @throws \Kirby\Exception\Exception On other errors
	 */
	public static function validateFile(string $file, $typeLazy = false): void
	{
		if (is_string($typeLazy) === true) {
			static::handler($typeLazy)->validateFile($file);
			return;
		}

		foreach (static::handlersForFile($file, $typeLazy === true) as $handler) {
			$handler->validateFile($file);
		}
	}

	/**
	 * Returns all handler objects that apply to the given file based on
	 * file extension and MIME type
	 *
	 * @param string $file
	 * @param bool $lazy If set to `true`, undefined handlers are skipped
	 * @return array<\Kirby\Sane\Handler>
	 */
	protected static function handlersForFile(string $file, bool $lazy = false): array
	{
		$handlers = $handlerClasses = [];

		// all values that can be used for the handler search;
		// filter out all empty options
		$options = array_filter([F::extension($file), F::mime($file)]);

		foreach ($options as $option) {
			$handler      = static::handler($option, $lazy);
			$handlerClass = $handler ? get_class($handler) : null;

			// ensure that each handler class is only returned once
			if ($handler && in_array($handlerClass, $handlerClasses) === false) {
				$handlers[]       = $handler;
				$handlerClasses[] = $handlerClass;
			}
		}

		return $handlers;
	}
}
