<?php

namespace Kirby\Data;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Spyc;
use Symfony\Component\Yaml\Yaml as Symfony;

/**
 * Simple Wrapper around the Spyc or Symfony YAML class
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
     * @param mixed $data
     * @return string
     */
    public static function encode($data): string
    {
        // get YAML handler from config option
        $handler = kirby()->option('yaml', 'spyc');

        if ($handler === 'symfony') {
            return Symfony::dump($data, 2, 2, Symfony::DUMP_MULTI_LINE_LITERAL_BLOCK | Symfony::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
        }

        if ($handler === 'spyc') {
            // TODO: The locale magic should no longer be
            //       necessary when support for PHP 7.x is dropped

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

        throw new InvalidArgumentException('Invalid YAML handler: ' . $handler);
    }

    /**
     * Parses an encoded YAML string and returns a multi-dimensional array
     *
     * @param mixed $string
     * @return array
     */
    public static function decode($string): array
    {
        if ($string === null || $string === '') {
            return [];
        }

        if (is_array($string) === true) {
            return $string;
        }

        if (is_string($string) === false) {
            throw new InvalidArgumentException('Invalid YAML data; please pass a string');
        }

        // get YAML handler from config option
        $handler = kirby()->option('yaml', 'spyc');

        // remove BOM
        $string = str_replace("\xEF\xBB\xBF", '', $string);

        if ($handler === 'symfony') {
            $result = Symfony::parse($string);
            $result = A::wrap($result);
            return $result;
        }

        if ($handler === 'spyc') {
            $result = Spyc::YAMLLoadString($string);

            if (is_array($result)) {
                return $result;
            } else {
                // apparently Spyc always returns an array, even for invalid YAML syntax
                // so this Exception should currently never be thrown
                throw new InvalidArgumentException('The YAML data cannot be parsed'); // @codeCoverageIgnore
            }
        }

        throw new InvalidArgumentException('Invalid YAML handler: ' . $handler);
    }
}
