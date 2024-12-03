<?php

namespace Kirby\Data;

use Kirby\Exception\Exception;
use Kirby\Filesystem\F;
use Throwable;

/**
 * The `Data` class provides readers and
 * writers for data. The class comes with
 * handlers for `json`, `php`, `txt`, `xml`
 * and `yaml` encoded data, but can be
 * extended and customized.
 *
 * The read and write methods automatically
 * detect which data handler to use in order
 * to correctly encode and decode passed data.
 *
 * @package   Kirby
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Data
{
	/**
	 * Handler Type Aliases
	 */
	public static array $aliases = [
		'md'    => 'txt',
		'mdown' => 'txt',
		'rss'   => 'xml',
		'yml'   => 'yaml',
	];

	/**
	 * All registered handlers
	 */
	public static array $handlers = [
		'json' => Json::class,
		'php'  => PHP::class,
		'txt'  => Txt::class,
		'xml'  => Xml::class,
		'yaml' => Yaml::class
	];

	/**
	 * Handler getter
	 */
	public static function handler(string $type): Handler
	{
		// normalize the type
		$type = strtolower($type);

		// find a handler or alias
		$handler = static::$handlers[$type] ?? null;

		if ($alias = static::$aliases[$type] ?? null) {
			$handler ??= static::$handlers[$alias] ?? null;
		}

		if ($handler === null || class_exists($handler) === false) {
			throw new Exception(
				message: 'Missing handler for type: "' . $type . '"'
			);
		}

		$handler = new $handler();

		if ($handler instanceof Handler === false) {
			throw new Exception(
				message: 'Handler for type: "' . $type . '" needs to extend ' . Handler::class
			);
		}

		return $handler;
	}

	/**
	 * Decodes data with the specified handler
	 */
	public static function decode(
		$string,
		string $type,
		bool $fail = true
	): array {
		try {
			return static::handler($type)->decode($string);
		} catch (Throwable $e) {
			if ($fail === false) {
				return [];
			}

			throw $e;
		}
	}

	/**
	 * Encodes data with the specified handler
	 */
	public static function encode($data, string $type): string
	{
		return static::handler($type)->encode($data);
	}

	/**
	 * Reads data from a file;
	 * the data handler is automatically chosen by
	 * the extension if not specified
	 */
	public static function read(
		string $file,
		string|null $type = null,
		bool $fail = true
	): array {
		try {
			$type  ??= F::extension($file);
			$handler = static::handler($type);
			return $handler->read($file);
		} catch (Throwable $e) {
			if ($fail === false) {
				return [];
			}

			throw $e;
		}
	}

	/**
	 * Writes data to a file;
	 * the data handler is automatically chosen by
	 * the extension if not specified
	 */
	public static function write(
		string $file,
		$data = [],
		string|null $type = null
	): bool {
		$type  ??= F::extension($file);
		$handler = static::handler($type);
		return $handler->write($file, $data);
	}
}
