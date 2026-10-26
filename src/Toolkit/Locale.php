<?php

namespace Kirby\Toolkit;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;

/**
 * PHP locale handling
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     3.5.0
 */
class Locale
{
	/**
	 * List of all locale constants supported by PHP
	 */
	public const array LOCALE_CONSTANTS = [
		'LC_COLLATE',
		'LC_CTYPE',
		'LC_MONETARY',
		'LC_NUMERIC',
		'LC_TIME',
		'LC_MESSAGES'
	];

	/**
	 * Builds an associative array with the locale
	 * categories that are actually supported on this system,
	 * without `LC_ALL`
	 * @since 7.0.0
	 *
	 * @return array<int, string>
	 */
	protected static function categories(): array
	{
		$constants = [];

		foreach (static::LOCALE_CONSTANTS as $name) {
			if (defined($name) === true) {
				$constants[constant($name)] = $name;
			}
		}

		return $constants;
	}

	/**
	 * Tries to convert an `LC_*` category name to its constant value
	 * @since 7.0.0
	 */
	protected static function category(int|string $category): int|string
	{
		if (
			is_string($category) === true &&
			Str::startsWith($category, 'LC_') === true &&
			defined($category) === true
		) {
			return constant($category);
		}

		// already an int or we cannot convert it safely
		return $category;
	}

	/**
	 * Converts a normalized locale array to an array with the
	 * locale constants replaced with their string representations
	 *
	 * @param array<int|string, string> $locale
	 * @return array<int|string, string>
	 */
	public static function export(array $locale): array
	{
		$result    = [];
		$constants = [LC_ALL => 'LC_ALL'] + static::categories();

		// replace the keys in the locale data array with the locale names
		foreach ($locale as $key => $value) {
			$result[$constants[$key] ?? $key] = $value;
		}

		return $result;
	}

	/**
	 * Returns the current locale value for
	 * a specified or for all locale categories
	 * @since 3.5.6
	 *
	 * @param $category Locale category constant or constant name
	 * @return array<int, string|false>|string Associative array if `LC_ALL` was passed (default), otherwise string
	 *
	 * @throws Exception If the locale cannot be determined
	 * @throws InvalidArgumentException If the provided locale category is invalid
	 */
	public static function get(int|string $category = LC_ALL): array|string
	{
		$constant = static::category($category);

		if (is_int($constant) !== true) {
			throw new InvalidArgumentException(
				message: 'Invalid locale category "' . $category . '"'
			);
		}

		if ($constant !== LC_ALL) {
			// `setlocale(..., 0)` actually *gets* the locale
			$locale = setlocale($constant, '0');

			if (is_string($locale) !== true) {
				throw new Exception(
					message: 'Could not determine locale for category "' . $category . '"'
				);
			}

			return $locale;
		}

		// no specific `$category` was passed, make a list of all locales
		$locales = [];

		foreach (array_keys(static::categories()) as $constant) {
			// `setlocale(..., 0)` actually *gets* the locale
			$locales[$constant] = setlocale($constant, '0');
		}

		// if all values are the same, we can use `LC_ALL`
		// instead of a long array with all constants
		if (count(array_unique($locales)) === 1) {
			return [
				LC_ALL => reset($locales)
			];
		}

		return $locales;
	}

	/**
	 * Converts a locale string or an array with constant or
	 * string keys to a normalized constant => value array
	 *
	 * @param array<int|string, string>|string $locale
	 * @return array<int|string, string>
	 */
	public static function normalize(mixed $locale): array
	{
		if (is_array($locale) === true) {
			// replace string constant keys with the constant values
			$result = [];

			foreach ($locale as $key => $value) {
				$result[static::category($key)] = $value;
			}

			return $result;
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
	 *
	 * @param array<int|string, string>|string $locale
	 */
	public static function set(array|string $locale): void
	{
		$locale = static::normalize($locale);

		// locale for core string functions
		foreach ($locale as $key => $value) {
			if (is_int($key) === false) {
				throw new InvalidArgumentException(
					message: 'Invalid locale category "' . $key . '"'
				);
			}

			setlocale($key, $value);
		}

		// locale for the intl extension
		if (
			function_exists('locale_set_default') === true &&
			$default = $locale[LC_TIME] ?? $locale[LC_ALL] ?? null
		) {
			locale_set_default($default);
		}
	}
}
