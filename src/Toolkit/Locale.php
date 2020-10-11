<?php

namespace Kirby\Toolkit;

use Kirby\Exception\InvalidArgumentException;

/**
 * PHP locale handling
 *
 * @package   Kirby Toolkit
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Locale
{
    /**
     * Converts a normalized locale array to an array with the
     * locale constants replaced with their string representations
     *
     * @param array $locale
     * @return array
     */
    public static function export(array $locale): array
    {
        // list of all possible constant names
        $constantNames = [
            'LC_ALL', 'LC_COLLATE', 'LC_CTYPE', 'LC_MONETARY',
            'LC_NUMERIC', 'LC_TIME', 'LC_MESSAGES'
        ];

        // build an associative array with the locales
        // that are actually supported on this system
        $constants = [];
        foreach ($constantNames as $name) {
            if (defined($name) === true) {
                $constants[constant($name)] = $name;
            }
        }

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
                if (is_string($key) === true && Str::startsWith($key, 'LC_') === true) {
                    $key = constant($key);
                }

                $convertedLocale[$key] = $value;
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
}
