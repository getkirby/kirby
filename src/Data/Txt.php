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

			$key          = Str::ucfirst(Str::slug($key));
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
		// avoid problems with arrays
		if (is_array($value) === true) {
			$value = Data::encode($value, 'yaml');
			// avoid problems with localized floats
		} elseif (is_float($value) === true) {
			$value = Str::float($value);
		}

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

		// multi-line content
		$result .= match (preg_match('!\R!', $value)) {
			1       => "\n\n",
			default => ' ',
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
			throw new InvalidArgumentException('Invalid TXT data; please pass a string');
		}

		// remove Unicode BOM at the beginning of the file
		if (Str::startsWith($string, "\xEF\xBB\xBF") === true) {
			$string = substr($string, 3);
		}

		// explode all fields by the line separator
		$fields = preg_split('!\n----\s*\n*!', $string);

		// start the data array
		$data = [];

		// loop through all fields and add them to the content
		foreach ($fields as $field) {
			if ($pos = strpos($field, ':')) {
				$key = strtolower(trim(substr($field, 0, $pos)));
				$key = str_replace(['-', ' '], '_', $key);

				// Don't add fields with empty keys
				if (empty($key) === true) {
					continue;
				}

				$value = trim(substr($field, $pos + 1));

				// unescape escaped dividers within a field
				$data[$key] = preg_replace(
					'!(?<=\n|^)\\\\----!',
					'----',
					$value
				);
			}
		}

		return $data;
	}
}
