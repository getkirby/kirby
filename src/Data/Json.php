<?php

namespace Kirby\Data;

use Kirby\Exception\InvalidArgumentException;

/**
 * Simple Wrapper around json_encode and json_decode
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Json extends Handler
{
	/**
	 * Converts an array to an encoded JSON string
	 */
	public static function encode($data): string
	{
		return json_encode(
			$data,
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);
	}

	/**
	 * Parses an encoded JSON string and returns a multi-dimensional array
	 */
	public static function decode($string): array
	{
		if ($string === null || $string === '') {
			return [];
		}

		if (is_array($string) === true) {
			return $string;
		}

		if (is_string($string) === false) {
			throw new InvalidArgumentException('Invalid JSON data; please pass a string');
		}

		$result = json_decode($string, true);

		if (is_array($result) === true) {
			return $result;
		}

		throw new InvalidArgumentException('JSON string is invalid');
	}
}
