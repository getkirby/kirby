<?php

namespace Kirby\Sane;

use Kirby\Exception\Exception;

/**
 * Sane handler for HTML files
 * @since 3.6.0
 *
 * @package   Kirby Sane
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Html extends Handler
{
    /**
     * HTML sanitization
     *
     * @param string $string
     * @return string
     */
    public static function sanitize(string $string): string
    {
        $doc = new DOM($string);
        $doc->sanitize();

        return $doc->innerHTML($doc->body());
    }

    /**
     * Validates file contents
     * (Not yet implemented)
     *
     * @param string $string
     * @return void
     */
    public static function validate(string $string): void
    {
        throw new Exception('HTML validation is not yet implemented');
    }
}
