<?php

namespace Kirby\Toolkit;

use Kirby\Toolkit\Url\Fragments;
use Kirby\Toolkit\Url\Hash;
use Kirby\Toolkit\Url\Host;
use Kirby\Toolkit\Url\Params;
use Kirby\Toolkit\Url\Path;
use Kirby\Toolkit\Url\Port;
use Kirby\Toolkit\Url\Query;
use Kirby\Toolkit\Url\Scheme;

/**
 * A set of handy methods to work with URLs
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Url
{
    /**
     * home path
     *
     * @var string
     */
    public static $home = '/';

    /**
     * url generator
     *
     * @var callable|null
     */
    public static $to = null;

    /**
     * the current url
     *
     * @var string|null
     */
    public static $current = null;

    /**
     * Returns the current url with all bells and whistles
     *
     * @return string
     */
    public static function current(): string
    {
        if (static::$current !== null) {
            return static::$current;
        }
        return static::$current = static::base() . A::get($_SERVER, 'REQUEST_URI');
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
     * Returns the home url if defined
     *
     * @return string
     */
    public static function home(): string
    {
        return static::$home;
    }

    /**
     * The url smart handler. Must be defined before
     *
     * @return string
     */
    public static function to(): string
    {
        return call_user_func_array(static::$to, func_get_args());
    }

    /**
     * Return the last url the user has been on if detectable
     *
     * @return string
     */
    public static function last(): string
    {
        return A::get($_SERVER, 'HTTP_REFERER', '');
    }

    /**
     * Returns given url or if not set, the current url
     *
     * @param  string|null $url
     * @return string
     */
    public static function retrieve(string $url = null): string
    {
        return $url === null ? static::current() : $url;
    }

    /**
     * Returns a url built from all provided parts
     *
     * @param  array       $parts
     * @param  string|null $url
     * @return string
     */
    public static function build(array $parts = [], string $url = null): string
    {
        $parts = static::parts($parts, $url);

        if (empty($parts['scheme']) === false) {
            $parts['scheme'] = $parts['scheme'] . '://';
        }
        if (empty($parts['port']) === false) {
            $parts['port'] = ':' . $parts['port'];
        }

        $result = [$parts['scheme'] . $parts['host'] . $parts['port']];

        if (empty($parts['fragments']) === false) {
            $result[] = implode('/', $parts['fragments']);
        }
        if (empty($parts['params']) === false) {
            $result[] = Params::toString($parts['params']);
        }
        if (empty($parts['query']) === false) {
            $result[] = '?' . Query::toString($parts['query']);
        }
        if (empty($parts['hash']) === false) {
            $parts['hash'] = '#' . $parts['hash'];
        }

        return implode('/', $result) . $parts['hash'];
    }

    /**
     * Returns array of parts with default fallback
     *
     * @param  array        $parts
     * @param  string|null  $url
     * @return array
     */
    public static function parts(array $parts = [], string $url = null): array
    {
        $url = static::retrieve($url);

        $defaults = [
            'scheme'    => Scheme::get($url),
            'host'      => Host::get($url),
            'port'      => Port::get($url),
            'fragments' => Fragments::get($url),
            'params'    => Params::get($url),
            'query'     => Query::get($url),
            'hash'      => Hash::get($url),
        ];

        return array_merge($defaults, $parts);
    }

    /**
     * Checks if an URL is absolute
     *
     * @param  string  $url
     * @return bool
     */
    public static function isAbsolute(string $url): bool
    {
        return (Str::startsWith($url, 'http://') === true ||
                Str::startsWith($url, 'https://') === true ||
                Str::startsWith($url, '//')) === true;
    }

    /**
     * Convert a relative path into an absolute URL
     *
     * @param  string       $path
     * @param  string|null  $home
     * @return string
     */
    public static function makeAbsolute(string $path, string $home = null): string
    {
        // don't convert absolute urls
        if (static::isAbsolute($path) === true) {
            return $path;
        }

        // build the full url
        $path = ltrim($path, '/');
        $home = $home ?? static::$home;

        if (empty($path) === true) {
            return $home;
        }

        return $home == '/' ? '/' . $path : $home . '/' . $path;
    }

    /**
     * Tries to fix a broken url without protocol
     *
     * @param  string $url
     * @return string
     */
    public static function fix(string $url): string
    {
        // make sure to not touch absolute urls
        return preg_match('!^(https|http|ftp)\:\/\/!i', $url) === 0 ? 'http://' . $url : $url;
    }

    /**
     * Returns the base url
     *
     * @param  string|null $url
     * @return string
     */
    public static function base(string $url = null): string
    {
        if ($url === null) {
            $port = A::get($_SERVER, 'SERVER_PORT');
            $port = in_array($port, [80, 443]) === false ? $port : null;
            return Scheme::get() . '://' . A::get($_SERVER, 'SERVER_NAME', A::get($_SERVER, 'SERVER_ADDR')) . ($port ? ':' . $port : '');
        }

        $port   = Port::get($url);
        $scheme = Scheme::get($url);
        $host   = Host::get($url) . (is_int($port) === true ? ':' . $port : '');
        return ($scheme ? $scheme . '://' : '') . $host;
    }

    /**
     * Shortens a URL
     * It removes http:// or https:// and uses Str::short afterwards
     *
     * <code>
     * echo Url::short('http://veryveryverylongurl.com', 30);
     * // output: veryveryverylongurl.com
     * </code>
     *
     * @param  string  $url     The URL to be shortened
     * @param  int     $length  The final number of characters the URL
     *                          should have
     * @param  bool    $base    True: only take the base of the URL.
     * @param  string  $rep     The element, which should be added if the
     *                          string is too long. Ellipsis is the default.
     * @return string           The shortened URL
     */
    public static function short(string $url, int $length = 0, bool $base = false, string $rep = '…'): string
    {
        if ($base === true) {
            $url = static::base($url);
        }

        // replace all the nasty stuff from the url
        $url = str_replace(['http://', 'https://', 'ftp://', 'www.'], '', $url);

        // try to remove the last / after the url
        $url = rtrim($url, '/');

        return ($length !== 0) ? Str::short($url, $length, $rep) : $url;
    }

    /**
     * Tries to convert an internationalized domain name to
     * the UTF8 representation
     * Requires the intl PHP extension
     *
     * @param  string $url
     * @return string
     */
    public static function idn(string $url): string
    {
        if (function_exists('idn_to_utf8') === false) {
            return $url;
        }
        if (static::isAbsolute($url) === true) {
            $url = static::short($url);
        }
        return idn_to_utf8($url);
    }

    /**
     * Returns the URL for document root no
     * matter what the path is.
     *
     * @return string
     */
    public static function index(): string
    {
        // TODO: use Server::cli again
        if (defined('STDIN') === true || (substr(PHP_SAPI, 0, 3) === 'cgi' && $term = getenv('TERM') && $term !== 'unknown')) {
            return '/';
        } else {
            return static::base() . preg_replace('!\/index\.php$!i', '', A::get($_SERVER, 'SCRIPT_NAME'));
        }
    }
}

// basic home url setup
Url::$home = Url::base();

// basic url generator setup
Url::$to = function ($path = '/'): string {
    if (Url::isAbsolute($path) === true) {
        return $path;
    }

    $path = ltrim($path, '/');

    if (empty($path) === true) {
        return Url::home();
    }

    return Url::home() . '/' . $path;
};
