<?php

namespace Kirby\Data\Handler;

use Kirby\Data\Handler;

/**
 * Simple Wrapper around serialize and unserialize
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Php extends Handler
{

    /**
     * Serializes an array
     *
     * @param  array  $data
     * @return string
     */
    public static function encode(array $data): string
    {
        return serialize($data);
    }

    /**
     * Unserializes a string
     *
     * @param  string     $string
     * @return array/null
     */
    public static function decode(string $string): ?array
    {
        $result = @unserialize($string);

        if (is_array($result)) {
            return $result;
        } else {
            return null;
        }
    }
}
