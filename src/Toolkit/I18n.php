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
	 *
	 * @var Closure
	 */
	public static $load = null;

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

		return $count === 1 ? 'singular' : 'plural';
	}

	/**
	 * Formats a number
	 */
	public static function formatNumber(int|float $number, string $locale = null): string
	{
		$locale  ??= static::locale();
		$formatter = static::decimalNumberFormatter($locale);
		$number    = $formatter?->format($number) ?? $number;
		return (string)$number;
	}

	/**
	 * Returns thecurrent locale code
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
	 * Translates a given message
	 * according to the currently set locale
	 */
	public static function translate(
		string|array|null $key,
		string|array $fallback = null,
		string $locale = null
	): string|array|Closure|null {
		// use current locale if no specific is passed
		$locale ??= static::locale();

		// array of translated strings ['en' => 'house', 'de' => 'Haus', …]
		if (is_array($key) === true) {
			return static::translateFromTranslations($locale, $key, $fallback);
		}

		// $key is a string, look up as key in translations table
		if ($result = static::translation($locale)[$key] ?? null) {
			return $result;
		}

		// if an explicit fallback has been passed
		if ($fallback !== null) {
			return $fallback;
		}

		// last resort: use translations for fallback locales
		foreach (static::fallbacks() as $fallback) {
			// skip locale if we have already tried
			if ($locale === $fallback) {
				continue;
			}

			if ($result = static::translation($fallback)[$key] ?? null) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * Choose translation from array based on the provided locale
	 *
	 * @param array $translations e.g. ['en' => 'house', 'de' => 'Haus', …]
	 */
	protected static function translateFromTranslations(
		string $locale,
		array $translations,
		string|array $fallback = null
	): string {
		// try to use actual locale or
		// shorter language code, e.g. `es` for `es_ES` locale
		$result   = $translations[$locale] ?? null;
		$result ??= $translations[Str::before($locale, '_')] ?? null;

		if ($result) {
			return $result;
		}

		// use global wildcard as i18n key
		if ($wildcard = $translations['*'] ?? null) {
			return static::translate($wildcard, $wildcard);
		}

		// use fallback
		if (is_array($fallback) === false) {
			return $fallback;
		}

		// try first fallback for locale, fallback for English as
		// global default or, lastly, just go with the first entry
		return
			$fallback[$locale] ??
			$fallback['en'] ??
			reset($fallback);
	}

	/**
	 * Translate by key and then replace
	 * placeholders in the text
	 */
	public static function template(
		string $key,
		string|array $fallback = null,
		array|null $replace = null,
		string|null $locale = null
	): string {
		if (is_array($fallback) === true) {
			$replace  = $fallback;
			$fallback = null;
			$locale   = null;
		}

		$template = static::translate($key, $fallback, $locale);

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
	public static function translation(string $locale = null): array
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
	protected static function decimalNumberFormatter(string $locale): NumberFormatter|null
	{
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
		string $locale = null,
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

		return str_replace('{{ count }}', $count, $message);
	}
}
