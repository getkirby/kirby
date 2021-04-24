<?php

namespace Kirby\Sane;

use Kirby\Exception\Exception;
use Kirby\Toolkit\F;

/**
 * Base handler abstract,
 * which needs to be extended to
 * create valid sane handlers
 *
 * @package   Kirby Sane
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Handler
{
    /**
     * Validates file contents
     *
     * @param string $string
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
     * @throws \Kirby\Exception\Exception On other errors
     */
    abstract public static function validate(string $string): void;

    /**
     * Validates the contents of a file
     *
     * @param string $file
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
     * @throws \Kirby\Exception\Exception On other errors
     */
    public static function validateFile(string $file): void
    {
        $contents = F::read($file);
        if ($contents === false) {
            throw new Exception('The file "' . $file . '" does not exist');
        }

        static::validate($contents);
    }
}
