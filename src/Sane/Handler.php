<?php

namespace Kirby\Sane;

use Kirby\Exception\Exception;
use Kirby\Filesystem\F;

/**
 * Base handler abstract,
 * which needs to be extended to
 * create valid sane handlers
 * @since 3.5.4
 *
 * @package   Kirby Sane
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Handler
{
	/**
	 * Sanitizes the given string
	 *
	 * @param string $string
	 * @return string
	 */
	abstract public static function sanitize(string $string): string;

	/**
	 * Sanitizes the contents of a file by overwriting
	 * the file with the sanitized version
	 *
	 * @param string $file
	 * @return void
	 *
	 * @throws \Kirby\Exception\Exception If the file does not exist
	 * @throws \Kirby\Exception\Exception On other errors
	 */
	public static function sanitizeFile(string $file): void
	{
		$sanitized = static::sanitize(static::readFile($file));
		F::write($file, $sanitized);
	}

	/**
	 * Validates file contents
	 *
	 * @param string $string
	 * @return void
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
	 * @throws \Kirby\Exception\Exception On other errors
	 */
	abstract public static function validate(string $string): void;

	/**
	 * Validates the contents of a file
	 *
	 * @param string $file
	 * @return void
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
	 * @throws \Kirby\Exception\Exception If the file does not exist
	 * @throws \Kirby\Exception\Exception On other errors
	 */
	public static function validateFile(string $file): void
	{
		static::validate(static::readFile($file));
	}

	/**
	 * Reads the contents of a file
	 * for sanitization or validation
	 *
	 * @param string $file
	 * @return string
	 *
	 * @throws \Kirby\Exception\Exception If the file does not exist
	 */
	protected static function readFile(string $file): string
	{
		$contents = F::read($file);

		if ($contents === false) {
			throw new Exception('The file "' . $file . '" does not exist');
		}

		return $contents;
	}
}
