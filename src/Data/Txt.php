<?php

namespace Kirby\Data;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Kirby Txt Data Handler
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Txt extends Handler
{
	/**
	 * Converts an array to an encoded Kirby txt string
	 */
	public static function encode($data): string
	{
		$result = [];

		foreach (A::wrap($data) as $key => $value) {
			if (empty($key) === true || $value === null) {
				continue;
			}

			$key          = ucfirst(Str::slug($key));
			$value        = static::encodeValue($value);
			$result[$key] = static::encodeResult($key, $value);
		}

		return implode("\n\n----\n\n", $result);
	}

	/**
	 * Helper for converting the value
	 */
	protected static function encodeValue(array|string|float $value): string
	{
		// avoid problems with certain values
		$value = match (true) {
			is_array($value) => Data::encode($value, 'yaml'),
			is_float($value) => Str::float($value),
			default          => $value
		};

		// escape accidental dividers within a field
		$value = preg_replace('!(?<=\n|^)----!', '\\----', $value);

		return $value;
	}

	/**
	 * Helper for converting the key and value to the result string
	 */
	protected static function encodeResult(string $key, string $value): string
	{
		$value = trim($value);
		$result = $key . ':';

		$result .= match (str_contains($value, "\n") || str_contains($value, "\r")) {
			// multi-line content
			true  => "\n\n",
			// single line content, just add space after colon
			false => ' ',
		};

		$result .= $value;

		return $result;
	}

	/**
	 * Parses a Kirby txt string and returns a multi-dimensional array
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
			throw new InvalidArgumentException(
				message: 'Invalid TXT data; please pass a string'
			);
		}

		// remove Unicode BOM at the beginning of the file
		if (str_starts_with($string, "\xEF\xBB\xBF") === true) {
			$string = substr($string, 3);
		}

		// explode all fields by the line separator
		$fields = preg_split('!\n----\s*\n*!', $string);

		// start the data array
		$data = [];

		// loop through all fields and add them to the content
		foreach ($fields as $field) {
			$pos = strpos($field, ':');

			if ($pos === false || $pos === 0) {
				continue;
			}

			$key = strtolower(trim(substr($field, 0, $pos)));
			$key = str_replace(['-', ' '], '_', $key);

			// Don't add fields with empty keys
			if (empty($key) === true) {
				continue;
			}

			$value = trim(substr($field, $pos + 1));

			// unescape escaped dividers within a field
			if (str_contains($value, '\\----') === true) {
				$value = preg_replace(
					'!(?<=\n|^)\\\\----!',
					'----',
					$value
				);
			}

			$data[$key] = $value;
		}

		return $data;
	}
}
