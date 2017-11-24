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
class Fragments
{

    /**
     * Returns the path without params
     *
     * @param  string|null $url
     * @return array
     */
    public static function get(string $url = null): array
    {
        $url  = Url::retrieve($url);
        $path = Path::get($url);

        if (empty($path) === true) {
            return [];
        }

        $fragments = [];
        foreach (explode('/', $path) as $part) {
            if (strpos($part, Params::separator()) === false) {
                $fragments[] = $part;
            }
        }
        return $fragments;
    }

    /**
     * Strips the fragments from the URL
     *
     * @param  string|null $url
     * @return string
     */
    public static function strip(string $url = null): string
    {
        $url = Url::retrieve($url);
        return Url::build(['fragments' => []], $url);
    }
}
