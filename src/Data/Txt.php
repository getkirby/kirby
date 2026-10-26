<?php

namespace Kirby\Data;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Kirby Txt Data Handler
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Txt extends Handler
{
	/**
	 * Converts an array to an encoded Kirby txt string
	 */
	public static function encode(mixed $data): string
	{
		$result = [];

		foreach (A::wrap($data) as $key => $value) {
			if ($key === '' || $value === null) {
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
	protected static function encodeValue(
		array|bool|float|int|string $value
	): string {
		$value = match (true) {
			$value === true  => 'true',
			$value === false => 'false',
			is_array($value) => $value === [] ? '' : Data::encode($value, 'yaml'),
			is_float($value) => Str::float($value) ?? '',
			default          => (string)$value
		};

		// escape accidental dividers within a field
		return preg_replace('!(?<=\n|^)----!', '\\----', $value) ?? $value;
	}

	/**
	 * Helper for converting the key and value to the result string
	 */
	protected static function encodeResult(string $key, string $value): string
	{
		$value     = trim($value);
		$separator = match (preg_match('!\R!', $value)) {
			// multi-line content
			1       => "\n\n",
			// single line content, just add space after colon
			default => ' ',
		};

		return $key . ':' . $separator . $value;
	}

	/**
	 * Parses a Kirby txt string and returns a multi-dimensional array
	 */
	public static function decode(mixed $string): array
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
		if (Str::startsWith($string, "\xEF\xBB\xBF") === true) {
			$string = substr($string, 3);
		}

		$fields = preg_split('!\n----\s*\n*!', $string);
		$data   = [];

		foreach ($fields as $field) {
			$pos = strpos($field, ':');

			if ($pos === false) {
				continue;
			}

			$key = strtolower(trim(substr($field, 0, $pos)));
			$key = str_replace(['-', ' '], '_', $key);

			if ($key === '') {
				continue;
			}

			$value = trim(substr($field, $pos + 1));

			// unescape escaped dividers within a field
			$data[$key] = preg_replace(
				'!(?<=\n|^)\\\\----!',
				'----',
				$value
			) ?? $value;
		}

		return $data;
	}
}
