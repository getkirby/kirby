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
class Host
{

    /**
     * Returns the schme for the given url
     *
     * @param  string|null  $url
     * @return string|false
     */
    public static function get(string $url = null)
    {
        $url = Url::retrieve($url);
        return parse_url($url, PHP_URL_HOST);
    }
}
