<?php

namespace Kirby\Data;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Kirby Txt Data Handler
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Txt extends Handler
{
    /**
     * Converts an array to an encoded Kirby txt string
     *
     * @param mixed $data
     * @return string
     */
    public static function encode($data): string
    {
        $result = [];

        foreach (A::wrap($data) as $key => $value) {
            if (empty($key) === true || $value === null) {
                continue;
            }

            $key          = Str::ucfirst(Str::slug($key));
            $value        = static::encodeValue($value);
            $result[$key] = static::encodeResult($key, $value);
        }

        return implode("\n\n----\n\n", $result);
    }

    /**
     * Helper for converting the value
     *
     * @param array|string $value
     * @return string
     */
    protected static function encodeValue($value): string
    {
        // avoid problems with arrays
        if (is_array($value) === true) {
            $value = Data::encode($value, 'yaml');
        // avoid problems with localized floats
        } elseif (is_float($value) === true) {
            $value = Str::float($value);
        }

        // escape accidental dividers within a field
        $value = preg_replace('!(?<=\n|^)----!', '\\----', $value);

        return $value;
    }

    /**
     * Helper for converting the key and value to the result string
     *
     * @param string $key
     * @param string $value
     * @return string
     */
    protected static function encodeResult(string $key, string $value): string
    {
        $result = $key . ':';

        // multi-line content
        if (preg_match('!\R!', $value) === 1) {
            $result .= "\n\n";
        } else {
            $result .= ' ';
        }

        $result .= trim($value);

        return $result;
    }

    /**
     * Parses a Kirby txt string and returns a multi-dimensional array
     *
     * @param mixed $string
     * @return array
     */
    public static function decode($string): array
    {
        if ($string === null) {
            return [];
        }

        if (is_array($string) === true) {
            return $string;
        }

        if (is_string($string) === false) {
            throw new InvalidArgumentException('Invalid TXT data; please pass a string');
        }

        // remove BOM
        $string = str_replace("\xEF\xBB\xBF", '', $string);
        // explode all fields by the line separator
        $fields = preg_split('!\n----\s*\n*!', $string);
        // start the data array
        $data = [];

        // loop through all fields and add them to the content
        foreach ($fields as $field) {
            $pos = strpos($field, ':');
            $key = str_replace(['-', ' '], '_', strtolower(trim(substr($field, 0, $pos))));

            // Don't add fields with empty keys
            if (empty($key) === true) {
                continue;
            }

            $value = trim(substr($field, $pos + 1));

            // unescape escaped dividers within a field
            $data[$key] = preg_replace('!(?<=\n|^)\\\\----!', '----', $value);
        }

        return $data;
    }
}
