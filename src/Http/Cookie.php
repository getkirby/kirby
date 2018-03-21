<?php

namespace Kirby\Http;

use Kirby\Util\A;
use Kirby\Util\Str;

/**
 * Cookie - This class makes cookie handling easy
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Cookie
{

    /**
     * Key to use for cookie signing
     * @var string
     */
    public static $key = 'KirbyHttpCookieKey';

    /**
     * Set a new cookie
     *
     * <code>
     *
     * cookie::set('mycookie', 'hello', 60);
     * // expires in 1 hour
     *
     * </code>
     *
     * @param  string  $key       The name of the cookie
     * @param  string  $value     The cookie content
     * @param  int     $lifetime  The number of minutes until the
     *                            cookie expires
     * @param  string  $path      The path on the server to set the
     *                            cookie for
     * @param  string  $domain    the domain
     * @param  boolean $secure    only sets the cookie over https
     * @param  boolean $httpOnly  avoids the cookie to be accessed
     *                            via javascript
     * @return boolean            true: cookie was created,
     *                            false: cookie creation failed
     */
    public static function set(string $key, string $value, int $lifetime = 0, string $path = '/', string $domain = null, bool $secure = false, bool $httpOnly = true): bool
    {
        // add an HMAC signature of the value
        $value = static::hmac($value) . '+' . $value;

        // store that thing in the cookie global
        $_COOKIE[$key] = $value;

        // store the cookie
        return setcookie($key, $value, static::lifetime($lifetime), $path, $domain, $secure, $httpOnly);
    }

    /**
     * Calculates the lifetime for a cookie
     *
     * @param  int  $minutes
     * @return int
     */
    public static function lifetime(int $minutes): int
    {
        return $minutes > 0 ? (time() + ($minutes * 60)) : 0;
    }

    /**
     * Stores a cookie forever
     *
     * <code>
     *
     * cookie::forever('mycookie', 'hello');
     * // never expires
     *
     * </code>
     *
     * @param  string  $key       The name of the cookie
     * @param  string  $value     The cookie content
     * @param  string  $path      The path on the server to set the
     *                            cookie for
     * @param  string  $domain    the domain
     * @param  boolean $secure    only sets the cookie over https
     * @param  boolean $httpOnly  avoids the cookie to be accessed
     *                            via javascript
     * @return boolean            true: cookie was created,
     *                            false: cookie creation failed
     */
    public static function forever(string $key, $value, string $path = '/', string $domain = null, bool $secure = false, bool $httpOnly = true): bool
    {
        return static::set($key, $value, 2628000, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Get a cookie value
     *
     * <code>
     *
     * cookie::get('mycookie', 'peter');
     * // sample output: 'hello' or if the cookie is not set 'peter'
     *
     * </code>
     *
     * @param  string|null  $key     The name of the cookie
     * @param  string|null  $default The default value, which should be returned
     *                               if the cookie has not been found
     * @return mixed                 The found value
     */
    public static function get(string $key = null, string $default = null)
    {
        if ($key === null) {
            return $_COOKIE;
        }
        $value = $_COOKIE[$key] ?? null;
        return empty($value) ? $default : static::parse($value);
    }

    /**
     * Checks if a cookie exists
     *
     * @param  string  $key
     * @return boolean
     */
    public static function exists(string $key): bool
    {
        return static::get($key) !== null;
    }

    /**
     * Creates a HMAC for the cookie value
     * Used as a cookie signature to prevent easy tampering with cookie data
     *
     * @param  string $value
     * @return string
     */
    protected static function hmac(string $value): string
    {
        return hash_hmac('sha1', $value, static::$key);
    }

    /**
     * Parses the hashed value from a cookie
     * and tries to extract the value
     *
     * @param  string $string
     * @return mixed
     */
    protected static function parse(string $string)
    {
        // if no hash-value seperator is present return null
        if (strpos($string, '+') === false) {
            return null;
        }

        // extract hash and value
        $parts = Str::split($string, '+');
        $hash  = A::first($parts);
        $value = A::last($parts);

        // if the hash or the value is missing at all return null
        if ($hash === $value) {
            return null;
        }

        // compare the extracted hash with the hashed value
        // don't accept value if the hash is invalid
        if (hash_equals(static::hmac($value), $hash) !== true) {
            return null;
        }

        return $value;
    }

    /**
     * Remove a cookie
     *
     * <code>
     *
     * cookie::remove('mycookie');
     * // mycookie is now gone
     *
     * </code>
     *
     * @param  string  $key The name of the cookie
     * @return boolean      true: the cookie has been removed,
     *                      false: the cookie could not be removed
     */
    public static function remove(string $key): bool
    {
        if (isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);
            return setcookie($key, '', time() - 3600, '/');
        }

        return false;
    }
}
