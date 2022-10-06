<?php

namespace Kirby\Data;

use Kirby\Exception\Exception;
use Kirby\Filesystem\F;

/**
 * Base handler abstract,
 * which needs to be extended to
 * create valid data handlers
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Handler
{
	/**
	 * Parses an encoded string and returns a multi-dimensional array
	 *
	 * @throws \Exception if the file can't be parsed
	 */
	abstract public static function decode($string): array;

	/**
	 * Converts an array to an encoded string
	 */
	abstract public static function encode($data): string;

	/**
	 * Reads data from a file
	 */
	public static function read(string $file): array
	{
		$contents = F::read($file);
		if ($contents === false) {
			throw new Exception('The file "' . $file . '" does not exist');
		}

		return static::decode($contents);
	}

	/**
	 * Writes data to a file
	 */
	public static function write(string $file, $data = []): bool
	{
		return F::write($file, static::encode($data));
	}
}
