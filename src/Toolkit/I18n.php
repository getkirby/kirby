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
	 * The fallback locale or a
	 * list of fallback locales
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
		if (
			is_array(static::$fallback) === true ||
			is_string(static::$fallback) === true
		) {
			return A::wrap(static::$fallback);
		}

		if (is_callable(static::$fallback) === true) {
			return static::$fallback = A::wrap((static::$fallback)());
		}

		return static::$fallback = ['en'];
	}

	/**
	 * Returns singular or plural
	 * depending on the given number
	 *
	 * @param bool $none If true, 'none' will be returned if the count is 0
	 */
	public static function form(int $count, bool $none = false): string
	{
		if ($none === true && $count === 0) {
			return 'none';
		}

		return $count === 1 ? 'singular' : 'plural';
	}

	/**
	 * Formats a number
	 */
	public static function formatNumber(int|float $number, string|null $locale = null): string
	{
		$locale    ??= static::locale();
		$formatter   = static::decimalNumberFormatter($locale);
		$number      = $formatter?->format($number) ?? $number;
		return (string)$number;
	}

	/**
	 * Returns the locale code
	 */
	public static function locale(): string
	{
		if (is_string(static::$locale) === true) {
			return static::$locale;
		}

		if (is_callable(static::$locale) === true) {
			return static::$locale = (static::$locale)();
		}

		return static::$locale = 'en';
	}

	/**
	 * Translates a given message
	 * according to the currently set locale
	 */
	public static function translate(
		string|array|null $key,
		string|array|null $fallback = null,
		string|null $locale = null
	): mixed {
		$locale ??= static::locale();

		if (is_array($key) === true) {
			// try to use actual locale
			if (isset($key[$locale])) {
				return $key[$locale];
			}
			// try to use language code, e.g. `es` when locale is `es_ES`
			$lang = Str::before($locale, '_');
			if (isset($key[$lang])) {
				return $key[$lang];
			}
			// use fallback
			if (is_array($fallback)) {
				return $fallback[$locale] ?? $fallback['en'] ?? reset($fallback);
			}
			return $fallback;
		}

		if ($translation = static::translation($locale)[$key] ?? null) {
			return $translation;
		}

		if ($fallback !== null) {
			return $fallback;
		}

		foreach (static::fallbacks() as $fallback) {
			// skip locales we have already tried
			if ($locale === $fallback) {
				continue;
			}

			if ($translation = static::translation($fallback)[$key] ?? null) {
				return $translation;
			}
		}

		return null;
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

		$template  = static::translate($key, $fallback, $locale);
		$replace ??= [];

		return Str::template($template, $replace, [
			'fallback' => '-',
			'start'    => '{',
			'end'      => '}'
		]);
	}

	/**
	 * Returns the current or any other translation
	 * by locale. If the translation does not exist
	 * yet, the loader will try to load it, if defined.
	 */
	public static function translation(string|null $locale = null): array
	{
		$locale ??= static::locale();

		if (isset(static::$translations[$locale]) === true) {
			return static::$translations[$locale];
		}

		if (is_a(static::$load, Closure::class) === true) {
			return static::$translations[$locale] = (static::$load)($locale);
		}

		// try to use language code, e.g. `es` when locale is `es_ES`
		$lang = Str::before($locale, '_');
		if (isset(static::$translations[$lang]) === true) {
			return static::$translations[$lang];
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
	protected static function decimalNumberFormatter(string $locale): NumberFormatter|null
	{
		if (isset(static::$decimalsFormatters[$locale])) {
			return static::$decimalsFormatters[$locale];
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
	): string|null {
		$locale    ??= static::locale();
		$translation = static::translate($key, null, $locale);

		if ($translation === null) {
			return null;
		}

		if (is_a($translation, Closure::class) === true) {
			return $translation($count);
		}

		if (is_string($translation) === true) {
			$message = $translation;
		} elseif (isset($translation[$count]) === true) {
			$message = $translation[$count];
		} else {
			$message = end($translation);
		}

		if ($formatNumber === true) {
			$count = static::formatNumber($count, $locale);
		}

		return str_replace('{{ count }}', (string)$count, $message);
	}
}
