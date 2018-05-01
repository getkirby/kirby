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
     * Needs to throw an Exception if the file can't be parsed.
     *
     * @param  string $string
     * @return array
     */
    abstract public static function decode($string): array;
}
