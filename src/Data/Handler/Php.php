<?php

namespace Kirby\Data\Handler;

use Exception;
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
     * Converts an array to a serialized string
     *
     * @param  array  $data
     * @return string
     */
    public static function encode(array $data): string
    {
        return serialize($data);
    }

    /**
     * Parses a serialized string and returns a multi-dimensional array
     *
     * @param  string $string
     * @return array
     */
    public static function decode($string): array
    {
        $result = @unserialize($string);

        if (is_array($result)) {
            return $result;
        } else {
            throw new Exception('Serialized string is invalid');
        }
    }
}
