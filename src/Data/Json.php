<?php

namespace Kirby\Data;

use Kirby\Exception\InvalidArgumentException;

/**
 * Simple Wrapper around json_encode and json_decode
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Json extends Handler
{
    /**
     * Converts an array to an encoded JSON string
     *
     * @param mixed $data
     * @return string
     */
    public static function encode($data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Parses an encoded JSON string and returns a multi-dimensional array
     *
     * @param string $json
     * @return array
     */
    public static function decode($json): array
    {
        if ($json === null) {
            return [];
        }

        if (is_array($json) === true) {
            return $json;
        }

        if (is_string($json) === false) {
            throw new InvalidArgumentException('Invalid JSON data. Please pass a string');
        }

        $result = json_decode($json, true);

        if (is_array($result) === true) {
            return $result;
        } else {
            throw new InvalidArgumentException('JSON string is invalid');
        }
    }
}
