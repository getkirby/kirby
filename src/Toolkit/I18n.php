<?php

namespace Kirby\Toolkit;

use Exception;

/**
 * Localization class, roughly inspired by VueI18n
 */
class I18n
{
    public static $locale = 'en';
    public static $translation = [];
    public static $fallback = [];

    public static function translate($key, $fallback = null)
    {
        if (is_array($key) === true) {
            if (isset($key[static::$locale])) {
                return $key[static::$locale];
            }

            if (is_array($fallback)) {
                return $fallback[static::$locale] ?? null;
            }

            return $fallback;
        }

        return static::$translation[$key] ?? static::$fallback[$key] ?? $fallback;
    }

    public static function translateCount($key, int $count)
    {
        $translation = static::translate($key);

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
