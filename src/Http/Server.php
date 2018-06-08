<?php

namespace Kirby\Http;

/**
 * A set of methods that make it more convenient to get variables
 * from the global server array
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Server
{

    /**
     * Returns the server's IP address
     *
     * @return string
     */
    public static function address(): string
    {
        return static::get('SERVER_ADDR');
    }

    /**
     * Checks if the request is being served by the CLI
     *
     * @return boolean
     */
    public static function cli(): bool
    {
        if (defined('STDIN') === true) {
            return true;
        }

        $term = getenv('TERM');

        if (substr(PHP_SAPI, 0, 3) === 'cgi' && $term && $term !== 'unknown') {
            return true;
        }

        return false;
    }

    /**
     * Gets a value from the _SERVER array
     *
     * <code>
     * Server::get('document_root');
     * // sample output: /var/www/kirby
     *
     * Server::get();
     * // returns the whole server array
     * </code>
     *
     * @param  mixed    $key     The key to look for. Pass false or null to
     *                           return the entire server array.
     * @param  mixed    $default Optional default value, which should be
     *                           returned if no element has been found
     * @return mixed
     */
    public static function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_SERVER;
        }

        $key   = strtoupper($key);
        $value = $_SERVER[$key] ?? $default;
        return static::sanitize($key, $value);
    }

    /**
     * Help to sanitize some _SERVER keys
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    public static function sanitize(string $key, $value)
    {
        switch ($key) {
            case 'SERVER_ADDR':
            case 'SERVER_NAME':
            case 'HTTP_HOST':
            case 'HTTP_X_FORWARDED_HOST':
                $value = strip_tags($value);
                $value = preg_replace('![^\w.:-]+!iu', '', $value);
                $value = trim($value, '-');
                $value = htmlspecialchars($value);
                break;
            case 'SERVER_PORT':
            case 'HTTP_X_FORWARDED_PORT':
                $value = intval(preg_replace('![^0-9]+!', '', $value));
                break;
        }

        return $value;
    }

    /**
     * Returns the correct port number
     *
     * @return int
     */
    public static function port(): int
    {
        $port = static::get('HTTP_X_FORWARDED_PORT');

        if (!empty($port)) {
            return $port;
        }

        return static::get('SERVER_PORT');
    }

    /**
     * Checks for a https request
     *
     * @return boolean
     */
    public static function https(): bool
    {
        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (static::port() === 443) {
            return true;
        } elseif (in_array(static::get('HTTP_X_FORWARDED_PROTO'), ['https', 'https, http'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the correct host
     *
     * @return string
     */
    public static function host(): string
    {
        $host = static::get('HTTP_X_FORWARDED_HOST');

        if (!empty($host)) {
            return $host;
        }

        $host = static::get('SERVER_NAME');

        if (!empty($host)) {
            return $host;
        }

        return static::get('SERVER_ADDR');
    }
}
