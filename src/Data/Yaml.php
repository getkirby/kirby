<?php

namespace Kirby\Data;

use Exception;
use Spyc;

/**
 * Simple Wrapper around Symfony's Yaml Component
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Yaml extends Handler
{

    /**
     * Converts an array to an encoded YAML string
     *
     * @param  mixed  $data
     * @return string
     */
    public static function encode($data): string
    {
        // fetch the current locale setting for numbers
        $locale = setlocale(LC_NUMERIC, 0);

        // change to english numerics to avoid issues with floats
        setlocale(LC_NUMERIC, 'C');

        // $data, $indent, $wordwrap, $no_opening_dashes
        $yaml = Spyc::YAMLDump($data, false, false, true);

        // restore the previous locale settings
        setlocale(LC_NUMERIC, $locale);

        return $yaml;
    }

    /**
     * Parses an encoded YAML string and returns a multi-dimensional array
     *
     * @param  string $string
     * @return array
     */
    public static function decode($yaml): array
    {
        if ($yaml === null) {
            return [];
        }

        if (is_array($yaml) === true) {
            return $yaml;
        }

        // remove BOM
        $yaml   = str_replace("\xEF\xBB\xBF", '', $yaml);
        $result = Spyc::YAMLLoadString($yaml);

        if (is_array($result)) {
            return $result;
        } else {
            // apparently Spyc always returns an array, even for invalid YAML syntax
            // so this Exception should currently never be thrown
            throw new Exception('YAML string is invalid'); // @codeCoverageIgnore
        }
    }
}
