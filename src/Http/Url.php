<?php

namespace Kirby\Http;

use Kirby\Toolkit\Str;

/**
 * Static URL tools
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Url
{

    /**
     * The base Url to build absolute Urls from
     *
     * @var string
     */
    public static $home = '/';

    /**
     * The current Uri object
     *
     * @var Uri
     */
    public static $current = null;

    /**
     * Facade for all Uri object methods
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, $arguments)
    {
        return (new Uri($arguments[0] ?? static::current()))->$method(...array_slice($arguments, 1));
    }

    /**
     * Url Builder
     * Actually just a factory for `new Uri($parts)`
     *
     * @param array $parts
     * @param string|null $url
     * @return string
     */
    public static function build(array $parts = [], string $url = null): string
    {
        return (string)(new Uri($url ?? static::current()))->clone($parts);
    }

    /**
     * Returns the current url with all bells and whistles
     *
     * @return string
     */
    public static function current(): string
    {
        return static::$current = static::$current ?? static::toObject()->toString();
    }

    /**
     * Returns the url for the current directory
     *
     * @return string
     */
    public static function currentDir(): string
    {
        return dirname(static::current());
    }

    /**
     * Tries to fix a broken url without protocol
     *
     * @param string $url
     * @return string
     */
    public static function fix(string $url = null): string
    {
        // make sure to not touch absolute urls
        return (!preg_match('!^(https|http|ftp)\:\/\/!i', $url)) ? 'http://' . $url : $url;
    }

    /**
     * Returns the home url if defined
     *
     * @return string
     */
    public static function home(): string
    {
        return static::$home;
    }

    /**
     * Returns the url to the executed script
     *
     * @param array $props
     * @param bool $forwarded
     * @return string
     */
    public static function index(array $props = [], bool $forwarded = false): string
    {
        return Uri::index($props, $forwarded)->toString();
    }

    /**
     * Checks if an URL is absolute
     *
     * @return boolean
     */
    public static function isAbsolute(string $url = null): bool
    {
        // matches the following groups of URLs:
        //  //example.com/uri
        //  http://example.com/uri, https://example.com/uri, ftp://example.com/uri
        //  mailto:example@example.com
        return preg_match('!^(//|[a-z0-9+-.]+://|mailto:|tel:)!i', $url) === 1;
    }

    /**
     * Convert a relative path into an absolute URL
     *
     * @param string $path
     * @param string $home
     * @return string
     */
    public static function makeAbsolute(string $path = null, string $home = null): string
    {
        if ($path === '' || $path === '/' || $path === null) {
            return $home ?? static::home();
        }

        if (substr($path, 0, 1) === '#') {
            return $path;
        }

        if (static::isAbsolute($path)) {
            return $path;
        }

        // build the full url
        $path = ltrim($path, '/');
        $home = $home ?? static::home();

        if (empty($path) === true) {
            return $home;
        }

        return $home === '/' ? '/' . $path : $home . '/' . $path;
    }

    /**
     * Returns the path for the given url
     *
     * @param string|array|null $url
     * @param bool $leadingSlash
     * @param bool $trailingSlash
     * @return xtring
     */
    public static function path($url = null, bool $leadingSlash = false, bool $trailingSlash = false): string
    {
        return Url::toObject($url)->path()->toString($leadingSlash, $trailingSlash);
    }

    /**
     * Returns the query for the given url
     *
     * @param string|array|null $url
     * @return string
     */
    public static function query($url = null): string
    {
        return Url::toObject($url)->query()->toString();
    }

    /**
     * Return the last url the user has been on if detectable
     *
     * @return string
     */
    public static function last(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    /**
     * Shortens the Url by removing all unnecessary parts
     *
     * @param string $url
     * @param int $length
     * @param boolean $base
     * @param string $rep
     * @return string
     */
    public static function short($url = null, int $length = 0, bool $base = false, string $rep = '…'): string
    {
        $uri = static::toObject($url);

        $uri->fragment = null;
        $uri->query    = null;
        $uri->password = null;
        $uri->port     = null;
        $uri->scheme   = null;
        $uri->username = null;

        // remove the trailing slash from the path
        $uri->slash = false;

        $url = $base ? $uri->base() : $uri->toString();
        $url = str_replace('www.', '', $url);

        return Str::short($url, $length, $rep);
    }

    /**
     * Removes the path from the Url
     *
     * @param string $url
     * @return string
     */
    public static function stripPath($url = null): string
    {
        return static::toObject($url)->setPath(null)->toString();
    }

    /**
     * Removes the query string from the Url
     *
     * @param string $url
     * @return string
     */
    public static function stripQuery($url = null): string
    {
        return static::toObject($url)->setQuery(null)->toString();
    }

    /**
     * Removes the fragment (hash) from the Url
     *
     * @param string $url
     * @return string
     */
    public static function stripFragment($url = null): string
    {
        return static::toObject($url)->setFragment(null)->toString();
    }

    /**
     * Smart resolver for internal and external urls
     *
     * @param string $path
     * @param mixed $options
     * @return string
     */
    public static function to(string $path = null, $options = null): string
    {
        // keep relative urls
        if (substr($path, 0, 2) === './' || substr($path, 0, 3) === '../') {
            return $path;
        }

        $url = static::makeAbsolute($path);

        if ($options === null) {
            return $url;
        }

        return (new Uri($url, $options))->toString();
    }

    /**
     * Converts the Url to a Uri object
     *
     * @param string $url
     * @return Kirby\Http\Uri
     */
    public static function toObject($url = null)
    {
        return $url === null ? Uri::current() : new Uri($url);
    }
}
