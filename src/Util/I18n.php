<?php

namespace Kirby\Util;

use Exception;

class I18n
{

    public static $locale = 'en';
    public static $translation = [];
    public static $fallback = [];

    public static function translate($key, string $fallback = null)
    {
        if (is_array($key) === true) {
            return $key[static::$locale] ?? $fallback;
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
