<?php

namespace Kirby\Data;

use Kirby\Exception\InvalidArgumentException;
use Spyc;

/**
 * Simple Wrapper around the Spyc YAML class
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * @deprecated 6.0.0 Use `Kirby\Data\YamlSymfony` instead
 */
class YamlSpyc
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
		$result = Spyc::YAMLLoadString($string);

		if (is_array($result) === true) {
			return $result;
		}

		// apparently Spyc always returns an array, even for invalid YAML syntax
		// so this Exception should currently never be thrown
		throw new InvalidArgumentException(message: 'The YAML data cannot be parsed'); // @codeCoverageIgnore
	}
}
