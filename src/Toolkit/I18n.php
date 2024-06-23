<?php

namespace Kirby\Toolkit;

use Closure;
use NumberFormatter;

/**
 * Localization class, roughly inspired by VueI18n
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class I18n
{
	/**
	 * Custom loader function
	 */
	public static Closure|null $load = null;

	/**
	 * Current locale
	 */
	public static string|Closure|null $locale = 'en';

	/**
	 * All registered translations
	 */
	public static array $translations = [];

	/**
	 * The fallback locale or a list of fallback locales
	 */
	public static string|array|Closure|null $fallback = ['en'];

	/**
	 * Cache of `NumberFormatter` objects by locale
	 */
	protected static array $decimalsFormatters = [];

	/**
	 * Returns the list of fallback locales
	 */
	public static function fallbacks(): array
	{
		if (is_callable(static::$fallback) === true) {
			static::$fallback = (static::$fallback)();
		}

		if (is_array(static::$fallback) === true) {
			return static::$fallback;
		}

		if (is_string(static::$fallback) === true) {
			return A::wrap(static::$fallback);
		}

		return static::$fallback = ['en'];
	}

	/**
	 * Returns singular or plural depending on the given number
	 *
	 * @param bool $none If true, 'none' will be returned if the count is 0
	 */
	public static function form(int $count, bool $none = false): string
	{
		if ($none === true && $count === 0) {
			return 'none';
		}

		return match ($count) {
			1       => 'singular',
			default => 'plural'
		};
	}

	/**
	 * Formats a number
	 */
	public static function formatNumber(
		int|float $number,
		string|null $locale = null
	): string {
		$locale  ??= static::locale();
		$formatter = static::decimalNumberFormatter($locale);
		$number    = $formatter?->format($number) ?? $number;
		return (string)$number;
	}

	/**
	 * Returns the current locale code
	 */
	public static function locale(): string
	{
		if (is_callable(static::$locale) === true) {
			static::$locale = (static::$locale)();
		}

		if (is_string(static::$locale) === true) {
			return static::$locale;
		}

		return static::$locale = 'en';
	}

	/**
	 * Translate by key and then replace
	 * placeholders in the text
	 */
	public static function template(
		string $key,
		string|array|null $fallback = null,
		array|null $replace = null,
		string|null $locale = null
	): string {
		if (is_array($fallback) === true) {
			$replace  = $fallback;
			$fallback = null;
			$locale   = null;
		}

		$template = static::translate($key, $fallback, $locale);

		return Str::template($template, $replace, ['fallback' => '-']);
	}

	/**
	 * Translates either a given i18n key from global translations
	 * or chooses correct entry from array of translations
	 * according to the currently set locale
	 */
	public static function translate(
		string|array|null $key,
		string|array|null $fallback = null,
		string|null $locale = null
	): string|array|Closure|null {
		// use current locale if no specific is passed
		$locale ??= static::locale();
		// create shorter locale code, e.g. `es` for `es_ES` locale
		$shortLocale = Str::before($locale, '_');

		// There are two main use cases that we will treat separately:
		// (1) with a string representing an i18n key to be looked up
		// (2) an array with entries per locale
		//
		// Both with various ways of handling fallbacks, provided
		// explicitly via the parameter and/or from global defaults.

		// (1) string $key: look up i18n string from global translations
		if (is_string($key) === true) {
			// look up locale in global translations list,
			if ($result = static::translation($locale)[$key] ?? null) {
				return $result;
			}

			// prefer any direct provided $fallback
			// over further fallback alternatives
			if ($fallback !== null) {
				if (is_array($fallback) === true) {
					return static::translate($fallback, null, $locale);
				}

				return $fallback;
			}

			// last resort: try using the fallback locales
			foreach (static::fallbacks() as $fallback) {
				// skip locale if we have already tried to save performance
				if ($locale === $fallback) {
					continue;
				}

				if ($result = static::translation($fallback)[$key] ?? null) {
					return $result;
				}
			}

			return null;
		}

		// --------
		// (2) array|null $key with entries per locale

		// try entry for long and short locale
		if ($result = $key[$locale] ?? null) {
			return $result;
		}
		if ($result = $key[$shortLocale] ?? null) {
			return $result;
		}

		// if the array as a global wildcard entry,
		// use this one as i18n key and try to resolve
		// this via part (1) of this method
		if ($wildcard = $key['*'] ?? null) {
			if ($result = static::translate($wildcard, $wildcard, $locale)) {
				return $result;
			}
		}

		// if the $fallback parameter is an array, we can assume
		// that it's also an array with entries per locale:
		// check with long and short locale if we find a matching entry
		if ($result = $fallback[$locale] ?? null) {
			return $result;
		}
		if ($result = $fallback[$shortLocale] ?? null) {
			return $result;
		}

		// all options for long/short actual locale have been exhausted,
		// revert to the list of fallback locales and try with each of them
		foreach (static::fallbacks() as $locale) {
			// first on the original input
			if ($result = $key[$locale] ?? null) {
				return $result;
			}
			// then on the fallback
			if ($result = $fallback[$locale] ?? null) {
				return $result;
			}
		}

		// if a string was provided as fallback, use that one
		if (is_string($fallback) === true) {
			return $fallback;
		}

		// otherwise the first array element of the input
		// or the first array element of the fallback
		if (is_array($key) === true) {
			return reset($key);
		}
		if (is_array($fallback) === true) {
			return reset($fallback);
		}

		return null;
	}

	/**
	 * Returns the current or any other translation
	 * by locale. If the translation does not exist
	 * yet, the loader will try to load it, if defined.
	 */
	public static function translation(string|null $locale = null): array
	{
		$locale ??= static::locale();

		if ($translation = static::$translations[$locale] ?? null) {
			return $translation;
		}

		if (static::$load instanceof Closure) {
			return static::$translations[$locale] = (static::$load)($locale);
		}

		// try to use language code, e.g. `es` when locale is `es_ES`
		if ($translation = static::$translations[Str::before($locale, '_')] ?? null) {
			return $translation;
		}

		return static::$translations[$locale] = [];
	}

	/**
	 * Returns all loaded or defined translations
	 */
	public static function translations(): array
	{
		return static::$translations;
	}

	/**
	 * Returns (and creates) a decimal number formatter for a given locale
	 */
	protected static function decimalNumberFormatter(
		string $locale
	): NumberFormatter|null {
		if ($formatter = static::$decimalsFormatters[$locale] ?? null) {
			return $formatter;
		}

		if (
			extension_loaded('intl') !== true ||
			class_exists('NumberFormatter') !== true
		) {
			return null; // @codeCoverageIgnore
		}

		return static::$decimalsFormatters[$locale] = new NumberFormatter($locale, NumberFormatter::DECIMAL);
	}

	/**
	 * Translates amounts
	 *
	 * Translation definition options:
	 * - Translation is a simple string: `{{ count }}` gets replaced in the template
	 * - Translation is an array with a value for each count: Chooses the correct template and
	 *   replaces `{{ count }}` in the template; if no specific template for the input count is
	 *   defined, the template that is defined last in the translation array is used
	 * - Translation is a callback with a `$count` argument: Returns the callback return value
	 *
	 * @param bool $formatNumber If set to `false`, the count is not formatted
	 */
	public static function translateCount(
		string $key,
		int $count,
		string|null $locale = null,
		bool $formatNumber = true
	) {
		$locale    ??= static::locale();
		$translation = static::translate($key, null, $locale);

		if ($translation === null) {
			return null;
		}

		if ($translation instanceof Closure) {
			return $translation($count);
		}

		$message = match (true) {
			is_string($translation)     => $translation,
			isset($translation[$count]) => $translation[$count],
			default 				    => end($translation)
		};

		if ($formatNumber === true) {
			$count = static::formatNumber($count, $locale);
		}

		return Str::template($message, compact('count'));
	}
}
