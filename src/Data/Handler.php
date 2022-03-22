<?php

namespace Kirby\Data;

use Kirby\Exception\Exception;
use Kirby\Filesystem\F;

/**
 * Base handler abstract,
 * which needs to be extended to
 * create valid data handlers
 *
 * @package   Kirby Data
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Handler
{
    /**
     * Parses an encoded string and returns a multi-dimensional array
     *
     * Needs to throw an Exception if the file can't be parsed.
     *
     * @param mixed $string
     * @return array
     */
    abstract public static function decode($string): array;

    /**
     * Converts an array to an encoded string
     *
     * @param mixed $data
     * @return string
     */
    abstract public static function encode($data): string;

    /**
     * Reads data from a file
     *
     * @param string $file
     * @return array
     */
    public static function read(string $file): array
    {
        $contents = F::read($file);
        if ($contents === false) {
            throw new Exception('The file "' . $file . '" does not exist');
        }

        return static::decode($contents);
    }

    /**
     * Writes data to a file
     *
     * @param string $file
     * @param mixed $data
     * @return bool
     */
    public static function write(string $file = null, $data = []): bool
    {
        return F::write($file, static::encode($data));
    }
}
