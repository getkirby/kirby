<?php

namespace Kirby\Data;

use Exception;
use Spyc;

/**
 * Simple Wrapper around Symfony's Yaml Component
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Yaml extends Handler
{

    /**
     * Converts an array to an encoded YAML string
     *
     * @param  array  $data
     * @return string
     */
    public static function encode(array $data): string
    {
        // $data, $indent, $wordwrap, $no_opening_dashes
        return Spyc::YAMLDump($data, false, false, true);
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

        $result = Spyc::YAMLLoadString($yaml);

        if (is_array($result)) {
            return $result;
        } else {
            throw new Exception('YAML string is invalid');
        }
    }
}
