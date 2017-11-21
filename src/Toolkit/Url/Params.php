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
class Params
{

    /**
     * Returns the params in the url
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

        $params = [];
        foreach (explode('/', $path) as $part) {
            $pos = strpos($part, static::separator());
            if ($pos === false) {
                continue;
            }
            $params[substr($part, 0, $pos)] = urldecode(substr($part, $pos + 1));
        }

        return $params;
    }

    /**
     * Strips the parameters from the URL
     *
     * @param  string|null $url
     * @return string
     */
    public static function strip(string $url = null): string
    {
        $url = Url::retrieve($url);
        return Url::build(['params' => []], $url);
    }

    /**
     * Converts parameters into string
     *
     * @param  array|null $params
     * @return string
     */
    public static function toString(array $params = null): string
    {
        if ($params === null) {
            $params = static::get();
        }
        $result = [];
        foreach ($params as $key => $val) {
            $result[] = $key . static::separator() . $val;
        }
        return implode('/', $result);
    }

    /**
     * Returns the correct separator for parameters
     * depending on the operating system
     *
     * @return string
     */
    public static function separator(): string
    {
        return DIRECTORY_SEPARATOR === '/' ? ';' : ':';
    }
}
