<?php

namespace Kirby\Data;

use Exception;

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
     * @param  mixed  $data
     * @return string
     */
    public static function encode($data): string
    {
        return json_encode($data);
    }

    /**
     * Parses an encoded JSON string and returns a multi-dimensional array
     *
     * @param  string $string
     * @return array
     */
    public static function decode($json): array
    {
        $result = json_decode($json, true);

        if (is_array($result) === true) {
            return $result;
        } else {
            throw new Exception('JSON string is invalid');
        }
    }
}
