<?php

namespace Kirby\Sane;

use Kirby\Exception\InvalidArgumentException;

/**
 * Sane handler for gzip-compressed SVGZ files
 *
 * @package   Kirby Sane
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Svgz extends Svg
{
    /**
     * Validates file contents
     *
     * @param string $string
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
     */
    public static function validate(string $string): void
    {
        // only support uncompressed files up to 10 MB to
        // prevent gzip bombs from crashing the process
        $uncompressed = @gzdecode($string, 10000000);

        if (is_string($uncompressed) !== true) {
            throw new InvalidArgumentException('Could not uncompress gzip data');
        }

        parent::validate($uncompressed);
    }
}
