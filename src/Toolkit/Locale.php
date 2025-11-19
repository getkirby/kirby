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
		'LC_COLLATE',
		'LC_CTYPE',
		'LC_MONETARY',
		'LC_NUMERIC',
		'LC_TIME',
		'LC_MESSAGES'
	];

	/**
	 * Converts a normalized locale array to an array with the
	 * locale constants replaced with their string representations
	 */
	public static function export(array $locale): array
	{
		$return    = [];
		$constants = static::supportedConstants(true);

		// replace the keys in the locale data array with the locale names
		foreach ($locale as $key => $value) {
			// use string representation for key
			// if it is a valid constant
			$return[$constants[$key] ?? $key] = $value;
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
	public static function get(int|string $category = LC_ALL): array|string
	{
		$normalizedCategory = static::normalizeConstant($category);

		if (is_int($normalizedCategory) !== true) {
			throw new InvalidArgumentException(
				message: 'Invalid locale category "' . $category . '"'
			);
		}

		if ($normalizedCategory !== LC_ALL) {
			// `setlocale(..., 0)` actually *gets* the locale
			$locale = setlocale($normalizedCategory, 0);

			if (is_string($locale) !== true) {
				throw new Exception(
					message: 'Could not determine locale for category "' . $category . '"'
				);
			}

			return $locale;
		}

		// no specific `$category` was passed, make a list of all locales
		$array = [];
		foreach (array_keys(static::supportedConstants()) as $constant) {
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
	 */
	public static function normalize($locale): array
	{
		if (is_array($locale) === true) {
			// replace string constant keys with the constant values
			$convertedLocale = [];

			foreach ($locale as $key => $value) {
				$convertedLocale[static::normalizeConstant($key)] = $value;
			}

			return $convertedLocale;
		}

		if (is_string($locale) === true) {
			return [LC_ALL => $locale];
		}

		throw new InvalidArgumentException(
			message: 'Locale must be string or array'
		);
	}

	/**
	 * Sets the PHP locale with a locale string or
	 * an array with constant or string keys
	 * @psalm-suppress UnusedFunctionCall
	 */
	public static function set(array|string $locale): void
	{
		$locale = static::normalize($locale);

		// locale for core string functions
		foreach ($locale as $key => $value) {
			setlocale($key, $value);
		}

		// locale for the intl extension
		if (
			function_exists('locale_set_default') === true &&
			$timeLocale = $locale[LC_TIME] ?? $locale[LC_ALL] ?? null
		) {
			locale_set_default($timeLocale);
		}
	}

	/**
	 * Tries to convert an `LC_*` constant name
	 * to its constant value
	 */
	protected static function normalizeConstant(
		int|string $constant
	): int|string {
		if (
			is_string($constant) === true &&
			Str::startsWith($constant, 'LC_') === true
		) {
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
