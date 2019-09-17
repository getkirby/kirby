<?php

namespace Kirby\Toolkit;

use Closure;
use Exception;

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
     * @var string
     */
    public static $locale = 'en';

    /**
     * All registered translations
     *
     * @var array
     */
    public static $translations = [];

    /**
     * The fallback locale
     *
     * @var string
     */
    public static $fallback = 'en';

    /**
     * Returns the fallback code
     *
     * @return string
     */
    public static function fallback(): string
    {
        if (is_string(static::$fallback) === true) {
            return static::$fallback;
        }

        if (is_callable(static::$fallback) === true) {
            return static::$fallback = (static::$fallback)();
        }

        return static::$fallback = 'en';
    }

    /**
     * Returns singular or plural
     * depending on the given number
     *
     * @param int $count
     * @param boolean $none If true, 'none' will be returned if the count is 0
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
        $locale = $locale ?? static::locale();

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

        if ($locale !== static::fallback()) {
            return static::translation(static::fallback())[$key] ?? null;
        }

        return null;
    }

    /**
     * Translate by key and then replace
     * placeholders in the text
     *
     * @param string $key
     * @param string $fallback
     * @param array $replace
     * @param string $locale
     * @return string
     */
    public static function template(string $key, $fallback = null, array $replace = null, string $locale = null)
    {
        if (is_array($fallback) === true) {
            $replace  = $fallback;
            $fallback = null;
            $locale   = null;
        }

        $template = static::translate($key, $fallback, $locale);
        return Str::template($template, $replace, '-', '{', '}');
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
        $locale = $locale ?? static::locale();

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
     * Translate amounts
     *
     * @param string $key
     * @param integer $count
     * @param string $locale
     * @return mixed
     */
    public static function translateCount(string $key, int $count, string $locale = null)
    {
        $translation = static::translate($key, null, $locale);

        if ($translation === null) {
            return null;
        }

        if (is_string($translation) === true) {
            return $translation;
        }

        if (count($translation) !== 3) {
            throw new Exception('Please provide 3 translations');
        }

        switch ($count) {
            case 0:
                $message = $translation[0];
                break;
            case 1:
                $message = $translation[1];
                break;
            default:
                $message = $translation[2];
        }

        return str_replace('{{ count }}', $count, $message);
    }
}
