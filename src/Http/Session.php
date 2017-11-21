<?php

namespace Kirby\Http;

use Kirby\Toolkit\Server;
use Kirby\Toolkit\Url\Scheme;

/**
 * Session - Handles all session fiddling
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Session
{
    /**
     * flag if session has been started
     * @var boolean
     */
    public static $started = false;

    /**
     * session name
     * @var string
     */
    public static $name = 'kirby_session';

    /**
     * session timeout
     * @var integer
     */
    public static $timeout = 30;

    /**
     * session cookies
     * @var array
     */
    public static $cookie = [];

    /**
     * fingerprint
     * @var string|callable
     */
    public static $fingerprint;

    /**
     * Starts a new session
     *
     * <code>
     *
     * Session::start();
     * // do whatever you want with the session now
     *
     * </code>
     *
     * @return boolean
     */
    public static function start(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        // store the session name
        static::$cookie += [
            'lifetime' => 0,
            'path'     => ini_get('session.cookie_path'),
            'domain'   => ini_get('session.cookie_domain'),
            'secure'   => (Scheme::get() === 'https'),
            'httponly' => true
        ];

        // set the custom session name
        session_name(static::$name);

        // make sure to use cookies only
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);

        // try to start the session
        if (!session_start()) {
            return false;
        }

        if (!setcookie(
            static::$name,
            session_id(),
            Cookie::lifetime(static::$cookie['lifetime']),
            static::$cookie['path'],
            static::$cookie['domain'],
            static::$cookie['secure'],
            static::$cookie['httponly']
        )) {
            return false;
        }

        // mark it as started
        static::$started = true;

        // check if the session is still valid
        if (!static::check()) {
            return static::destroy();
        }

        return true;
    }

    /**
     * Checks if the session is still valid
     * and not expired
     *
     * @return boolean
     */
    public static function check(): bool
    {
        // check for the last activity and compare it with the session timeout
        if (isset($_SESSION[static::$name . '_activity']) && time() - $_SESSION[static::$name . '_activity'] > static::$timeout * 60) {
            return false;
        }

        // check for an existing fingerprint and compare it
        if (isset($_SESSION[static::$name . '_fingerprint']) && $_SESSION[static::$name . '_fingerprint'] !== static::fingerprint()) {
            return false;
        }

        // store a new fingerprint and the last activity
        $_SESSION[static::$name . '_fingerprint'] = static::fingerprint();
        $_SESSION[static::$name . '_activity']    = time();

        return true;
    }

    /**
     * Generates a fingerprint from the user agent string
     *
     * @return string
     */
    public static function fingerprint(): string
    {

        // custom fingerprint callback
        if (is_callable(static::$fingerprint)) {
            return call_user_func(static::$fingerprint);
        }

        /// TODO: implement cli detection
        // if (!Server::cli()) {
        //    return sha1(Visitor::ua() . (ip2long($_SERVER['REMOTE_ADDR']) & ip2long('255.255.0.0')));
        //}

        return '';
    }

    /**
     * Returns the current session id
     *
     * @return string
     */
    public static function id(): string
    {
        static::start();
        return session_id();
    }

    /**
     * Sets a session value by key
     *
     * <code>
     *
     * Session::set('username', 'bastian');
     * // saves the username in the session
     *
     * Session::set(array(
     *     'key1' => 'val1',
     *     'key2' => 'val2',
     *     'key3' => 'val3'
     * ));
     * // setting multiple variables at once
     *
     * </code>
     *
     * @param  mixed   $key    The key to define
     * @param  mixed   $value  The value for the passed key
     * @return mixed
     */
    public static function set($key, $value = false)
    {
        static::start();

        if (!isset($_SESSION)) {
            return false;
        }
        if (is_array($key)) {
            return $_SESSION = array_merge($_SESSION, $key);
        }

        return $_SESSION[$key] = $value;
    }

    /**
     * Gets a session value by key
     *
     * <code>
     *
     * Session::get('username', 'bastian');
     * // saves the username in the session
     *
     * echo Session::get('username');
     * // output: 'bastian'
     *
     * </code>
     *
     * @param  string|null  $key       The key to look for. Pass false or null
     *                                 to return the entire session array.
     * @param  mixed         $default  Optional default value, which should be
     *                                 returned if no element has been found
     * @return mixed
     */
    public static function get(string $key = null, $default = null)
    {
        static::start();

        if (!isset($_SESSION)) {
            return false;
        }
        if ($key === null) {
            return $_SESSION;
        }

        return $_SESSION[$key] ?? $default;
    }

    /**
     * Retrieves an item and removes it afterwards
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function pull(string $key, $default = null)
    {
        $value = static::get($key, $default);
        static::remove($key);
        return $value;
    }

    /**
     * Removes a value from the session by key
     *
     * <code>
     *
     * $_SESSION = array(
     *     'username' => 'bastian',
     *     'id' => 1,
     * );
     *
     * Session::remove('username');
     * // $_SESSION = array(
     * //    'id' => 1
     * // )
     *
     * </code>
     *
     * @param  string $key The key to remove by
     * @return array       The session array without the value
     */
    public static function remove(string $key): array
    {
        static::start();

        unset($_SESSION[$key]);
        return $_SESSION;
    }

    /**
     * Checks if the session has already been started
     *
     * @return boolean
     */
    public static function started(): bool
    {
        return static::$started;
    }

    /**
     * Destroys a session
     *
     * <code>
     *
     * Session::start();
     * // do whatever you want with the session now
     *
     * Session::destroy();
     * // everything stored in the session will be deleted
     *
     * </code>
     *
     * @return boolean
     */
    public static function destroy(): bool
    {
        if (!static::$started) {
            return false;
        }

        $_SESSION = [];

        Cookie::remove(static::$name);

        static::$started = false;

        return session_destroy();
    }

    /**
     * Alternative for Session::destroy()
     *
     * @return boolean
     */
    public static function stop(): bool
    {
        return static::destroy();
    }

    /**
     * Destroys a session first and then starts it again
     */
    public static function restart()
    {
        static::destroy();
        static::start();
    }

    /**
     * Create a new session Id
     */
    public static function regenerateId()
    {
        static::start();
        session_regenerate_id(true);
    }
}
