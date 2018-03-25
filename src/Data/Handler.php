<?php

namespace Kirby\Data;

/**
 * Base handler abstract,
 * which needs to be extended to
 * create valid data handlers
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
abstract class Handler
{

    /**
     * Converts an array to an encoded string
     *
     * @param  array  $data
     * @return string
     */
    abstract public static function encode(array $data): string;

    /**
     * Parses an encoded string and returns a multi-dimensional array
     *
     * @param  string     $string
     * @return array/null
     */
    abstract public static function decode(string $string): ?array;
}
