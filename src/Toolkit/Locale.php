<?php

namespace Kirby\Toolkit;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;

/**
 * PHP locale handling
 * @since 3.5.0
 *
 * @package   Kirby Toolkit
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Locale
{
	/**
	 * List of all locale constants supported by PHP
	 */
	public const LOCALE_CONSTANTS = [
		'LC_COLLATE', 'LC_CTYPE', 'LC_MONETARY',
		'LC_NUMERIC', 'LC_TIME', 'LC_MESSAGES'
	];

	/**
	 * Converts a normalized locale array to an array with the
	 * locale constants replaced with their string representations
	 *
	 * @param array $locale
	 * @return array
	 */
	public static function export(array $locale): array
	{
		$constants = static::supportedConstants(true);

		// replace the keys in the locale data array with the locale names
		$return = [];
		foreach ($locale as $key => $value) {
			if (isset($constants[$key]) === true) {
				// the key is a valid constant,
				// replace it with its string representation
				$return[$constants[$key]] = $value;
			} else {
				// not found, keep it as-is
				$return[$key] = $value;
			}
		}

		return $return;
	}

	/**
	 * Returns the current locale value for
	 * a specified or for all locale categories
	 * @since 3.5.6
	 *
	 * @param int|string $category Locale category constant or constant name
	 * @return array|string Associative array if `LC_ALL` was passed (default), otherwise string
	 *
	 * @throws \Kirby\Exception\Exception If the locale cannot be determined
	 * @throws \Kirby\Exception\InvalidArgumentException If the provided locale category is invalid
	 */
	public static function get($category = LC_ALL)
	{
		$normalizedCategory = static::normalizeConstant($category);

		if (is_int($normalizedCategory) !== true) {
			throw new InvalidArgumentException('Invalid locale category "' . $category . '"');
		}

		if ($normalizedCategory !== LC_ALL) {
			// `setlocale(..., 0)` actually *gets* the locale
			$locale = setlocale($normalizedCategory, 0);

			if (is_string($locale) !== true) {
				throw new Exception('Could not determine locale for category "' . $category . '"');
			}

			return $locale;
		}

		// no specific `$category` was passed, make a list of all locales
		$array = [];
		foreach (static::supportedConstants() as $constant => $name) {
			// `setlocale(..., 0)` actually *gets* the locale
			$array[$constant] = setlocale($constant, '0');
		}

		// if all values are the same, we can use `LC_ALL`
		// instead of a long array with all constants
		if (count(array_unique($array)) === 1) {
			return [
				LC_ALL => array_shift($array)
			];
		}

		return $array;
	}

	/**
	 * Converts a locale string or an array with constant or
	 * string keys to a normalized constant => value array
	 *
	 * @param array|string $locale
	 * @return array
	 */
	public static function normalize($locale): array
	{
		if (is_array($locale)) {
			// replace string constant keys with the constant values
			$convertedLocale = [];
			foreach ($locale as $key => $value) {
				$convertedLocale[static::normalizeConstant($key)] = $value;
			}

			return $convertedLocale;
		} elseif (is_string($locale)) {
			return [LC_ALL => $locale];
		} else {
			throw new InvalidArgumentException('Locale must be string or array');
		}
	}

	/**
	 * Sets the PHP locale with a locale string or
	 * an array with constant or string keys
	 *
	 * @param array|string $locale
	 * @return void
	 */
	public static function set($locale): void
	{
		$locale = static::normalize($locale);

		foreach ($locale as $key => $value) {
			setlocale($key, $value);
		}
	}

	/**
	 * Tries to convert an `LC_*` constant name
	 * to its constant value
	 *
	 * @param int|string $constant
	 * @return int|string
	 */
	protected static function normalizeConstant($constant)
	{
		if (is_string($constant) === true && Str::startsWith($constant, 'LC_') === true) {
			return constant($constant);
		}

		// already an int or we cannot convert it safely
		return $constant;
	}

	/**
	 * Builds an associative array with the locales
	 * that are actually supported on this system
	 *
	 * @param bool $withAll If set to `true`, `LC_ALL` is returned as well
	 * @return array
	 */
	protected static function supportedConstants(bool $withAll = false): array
	{
		$names = static::LOCALE_CONSTANTS;
		if ($withAll === true) {
			array_unshift($names, 'LC_ALL');
		}

		$constants = [];
		foreach ($names as $name) {
			if (defined($name) === true) {
				$constants[constant($name)] = $name;
			}
		}

		return $constants;
	}
}
