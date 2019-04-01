<?php

namespace Kirby\Data;

use Exception;
use Kirby\Toolkit\F;

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
     * Parses an encoded string and returns a multi-dimensional array
     *
     * Needs to throw an Exception if the file can't be parsed.
     *
     * @param  string $string
     * @return array
     */
    abstract public static function decode($string): array;

    /**
     * Converts an array to an encoded string
     *
     * @param  mixed  $data
     * @return string
     */
    abstract public static function encode($data): string;

    /**
     * Reads data from a file
     *
     * @param  string $file
     * @return array
     */
    public static function read(string $file): array
    {
        if (file_exists($file) !== true) {
            throw new Exception('The file "' . $file . '" does not exist');
        }

        return static::decode(F::read($file));
    }

    /**
     * Writes data to a file.
     * The data handler is automatically chosen by
     * the extension if not specified.
     *
     * @param  array    $data
     * @return boolean
     */
    public static function write(string $file = null, array $data = []): bool
    {
        return F::write($file, static::encode($data));
    }
}
