<?php

namespace Kirby\Data;

use Kirby\Exception\InvalidArgumentException;
use Spyc;

/**
 * Simple Wrapper around the Spyc YAML class
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Yaml extends Handler
{
	/**
	 * Converts an array to an encoded YAML string
	 */
	public static function encode($data): string
	{
		// $data, $indent, $wordwrap, $no_opening_dashes
		return Spyc::YAMLDump($data, false, false, true);
	}

	/**
	 * Parses an encoded YAML string and returns a multi-dimensional array
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
			throw new InvalidArgumentException('Invalid YAML data; please pass a string');
		}

		// remove BOM
		$string = str_replace("\xEF\xBB\xBF", '', $string);
		$result = Spyc::YAMLLoadString($string);

		if (is_array($result) === true) {
			return $result;
		}

		// apparently Spyc always returns an array, even for invalid YAML syntax
		// so this Exception should currently never be thrown
		throw new InvalidArgumentException('The YAML data cannot be parsed'); // @codeCoverageIgnore
	}
}
