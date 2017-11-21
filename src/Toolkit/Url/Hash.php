<?php

namespace Kirby\Toolkit\Url;

use Kirby\Toolkit\Url;

/**
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Hash
{

    /**
     * Returns the hash of the given url
     *
     * @param  string|null  $url
     * @return string|false
     */
    public static function get(string $url = null)
    {
        $url = Url::retrieve($url);
        return parse_url($url, PHP_URL_FRAGMENT);
    }

    /**
     * Strips a hash value from the URL
     *
     * <code>
     * echo Hash::stripHash('http://testurl.com/#somehash');
     * // output: http://testurl.com/
     * </code>
     *
     * @param  string|null $url
     * @return string
     */
    public static function strip(string $url = null): string
    {
        $url = Url::retrieve($url);
        return Url::build(['hash' => ''], $url);
    }
}
