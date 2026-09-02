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
	 *
	 * Data files are rewritten as a whole. The shared lock makes sure that
	 * a read cannot land in the middle of a write and decode a truncated
	 * file as if it were the real content.
	 */
	public static function read(string $file): array
	{
		$contents = F::read($file, lock: true);

		if ($contents === false) {
			throw new Exception(
				message: 'The file "' . $file . '" does not exist or cannot be read'
			);
		}

		return static::decode($contents);
	}

	/**
	 * Writes data to a file
	 *
	 * The counterpart to the locked read above: `F::update()` takes the
	 * exclusive lock before it truncates, while `file_put_contents()` with
	 * `LOCK_EX` opens the stream in mode `w` and has already emptied the
	 * file by the time it takes the lock.
	 */
	public static function write(string $file, $data = []): bool
	{
		// encode before the file is opened: if encoding fails, an existing
		// file has to stay untouched and no empty file may be left behind
		$contents = static::encode($data);

		return F::update($file, fn (): string => $contents);
	}
}
