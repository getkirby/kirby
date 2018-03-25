<?php

namespace Kirby\Data\Handler;

use Kirby\Data\Handler;

/**
 * Simple Wrapper around json_encode and json_decode
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Json extends Handler
{

    /**
     * Converts an array to a json string
     *
     * @param  array  $data
     * @return string
     */
    public static function encode(array $data): string
    {
        return json_encode($data);
    }

    /**
     * Parses JSON and returns a multi-dimensional array
     *
     * @param  string     $string
     * @return array/null
     */
    public static function decode(string $json): ?array
    {
        $result = json_decode($json, true);

        if (is_array($result)) {
            return $result;
        } else {
            return null;
        }
    }
}
