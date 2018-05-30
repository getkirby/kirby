<?php

namespace Kirby\Toolkit\Url;

use Kirby\Toolkit\Str;
use Kirby\Toolkit\Url;

/**
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Query
{

    /**
     * Returns the query as array
     *
     * @param  string|null $url
     * @return array
     */
    public static function get(string $url = null): array
    {
        $url = Url::retrieve($url);
        parse_str(parse_url($url, PHP_URL_QUERY), $array);
        return $array;
    }

    /**
     * Checks if the url contains a query string
     *
     * @param  string|null $url
     * @return bool
     */
    public static function in(string $url = null): bool
    {
        $url = Url::retrieve($url);
        return Str::contains($url, '?') === true;
    }

    /**
     * Strips the query from the URL
     *
     * <code>
     * echo Query::stripQuery('http://www.youtube.com/watch?v=9q_aXttJduk');
     * // output: http://www.youtube.com/watch
     * </code>
     *
     * @param  string|null $url
     * @return string
     */
    public static function strip(string $url = null): string
    {
        $url = Url::retrieve($url);
        return Url::build(['query' => []], $url);
    }

    /**
     * Converts query into string
     *
     * @param  array|null $query
     * @return string
     */
    public static function toString(array $query = null): string
    {
        if ($query === null) {
            $query = static::get();
        }
        return http_build_query($query);
    }
}
