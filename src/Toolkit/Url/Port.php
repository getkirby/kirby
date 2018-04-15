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
class Port
{

    /**
     * Returns the port for the given url
     *
     * @param  string|null $url
     * @return int|false
     */
    public static function get(string $url = null)
    {
        $url  = Url::retrieve($url);
        $port = intval(parse_url($url, PHP_URL_PORT));
        return ($port >= 1 && $port <= 65535) ? $port : false;
    }
}
