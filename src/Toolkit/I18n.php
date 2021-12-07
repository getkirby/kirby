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
 * @copyright Bastian Allgeier GmbH
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
     *
     * @var string|\Closure
     */
    public static $locale = 'en';

    /**
     * All registered translations
     *
     * @var array
     */
    public static $translations = [];

    /**
     * The fallback locale or a
     * list of fallback locales
     *
     * @var string|array|\Closure
     */
    public static $fallback = ['en'];

    /**
     * Cache of `NumberFormatter` objects by locale
     *
     * @var array
     */
    protected static $decimalsFormatters = [];

    /**
     * Returns the first fallback locale
     *
     * @deprecated 3.5.1 Use `\Kirby\Toolkit\I18n::fallbacks()` instead
     * @todo Remove in 3.7.0
     *
     * @return string
     */
    public static function fallback(): string
    {
        // @codeCoverageIgnoreStart
        deprecated('I18n::fallback() has been deprecated. Use I18n::fallbacks() instead.');
        return static::fallbacks()[0];
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the list of fallback locales
     *
     * @return array
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
     * @param int $count
     * @param bool $none If true, 'none' will be returned if the count is 0
     * @return string
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
     *
     * @param int|float $number
     * @param string $locale
     * @return string
     */
    public static function formatNumber($number, string $locale = null): string
    {
        $locale ??= static::locale();

        $formatter = static::decimalNumberFormatter($locale);
        if ($formatter !== null) {
            $number = $formatter->format($number);
        }

        return (string)$number;
    }

    /**
     * Returns the locale code
     *
     * @return string
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
     *
     * @param string|array $key
     * @param string|array|null $fallback
     * @param string|null $locale
     * @return string|array|null
     */
    public static function translate($key, $fallback = null, string $locale = null)
    {
        $locale ??= static::locale();

        if (is_array($key) === true) {
            if (isset($key[$locale])) {
                return $key[$locale];
            }
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
     *
     * @param string $key
     * @param string|array|null $fallback
     * @param array|null $replace
     * @param string|null $locale
     * @return string
     */
    public static function template(string $key, $fallback = null, ?array $replace = null, ?string $locale = null): string
    {
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
     *
     * @param string|null $locale
     * @return array
     */
    public static function translation(string $locale = null): array
    {
        $locale ??= static::locale();

        if (isset(static::$translations[$locale]) === true) {
            return static::$translations[$locale];
        }

        if (is_a(static::$load, 'Closure') === true) {
            return static::$translations[$locale] = (static::$load)($locale);
        }

        return static::$translations[$locale] = [];
    }

    /**
     * Returns all loaded or defined translations
     *
     * @return array
     */
    public static function translations(): array
    {
        return static::$translations;
    }

    /**
     * Returns (and creates) a decimal number formatter for a given locale
     *
     * @return \NumberFormatter|null
     */
    protected static function decimalNumberFormatter(string $locale): ?NumberFormatter
    {
        if (isset(static::$decimalsFormatters[$locale])) {
            return static::$decimalsFormatters[$locale];
        }

        if (extension_loaded('intl') !== true || class_exists('NumberFormatter') !== true) {
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
     * @param string $key
     * @param int $count
     * @param string|null $locale
     * @param bool $formatNumber If set to `false`, the count is not formatted
     * @return mixed
     */
    public static function translateCount(string $key, int $count, string $locale = null, bool $formatNumber = true)
    {
        $locale    ??= static::locale();
        $translation = static::translate($key, null, $locale);

        if ($translation === null) {
            return null;
        }

        if (is_a($translation, 'Closure') === true) {
            return $translation($count);
        }

        if (is_string($translation) === true) {
            $message = $translation;
        } else {
            if (isset($translation[$count]) === true) {
                $message = $translation[$count];
            } else {
                $message = end($translation);
            }
        }

        if ($formatNumber === true) {
            $count = static::formatNumber($count, $locale);
        }

        return str_replace('{{ count }}', $count, $message);
    }
}
