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
class Path
{

    /**
     * Returns only the cleaned path of the url
     *
     * @param  string|null $url
     * @return string
     */
    public static function get(string $url = null): string
    {
        $url = Url::retrieve($url);

        // if a path is passed, let's pretend this is an absolute url
        // to trick the url parser. It's a bit hacky but it works
        if (Url::isAbsolute($url) === false) {
            $url = 'http://0.0.0.0/' . $url;
        }

        return trim(parse_url($url, PHP_URL_PATH), '/');
    }

    /**
     * Strips the path from the URL
     *
     * @param  string|null $url
     * @return string
     */
    public static function strip(string $url = null): string
    {
        $url = Url::retrieve($url);
        return Url::build(['fragments' => [], 'params' => []], $url);
    }
}
