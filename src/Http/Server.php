<?php

namespace Kirby\Http;

/**
 * A set of methods that make it more convenient to get variables
 * from the global server array
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Server
{
    /**
     * Cache for the cli status
     *
     * @var bool|null
     */
    public static $cli;

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
     * @return bool
     */
    public static function cli(): bool
    {
        if (static::$cli !== null) {
            return static::$cli;
        }

        if (defined('STDIN') === true) {
            return static::$cli = true;
        }

        $term = getenv('TERM');

        if (substr(PHP_SAPI, 0, 3) === 'cgi' && $term && $term !== 'unknown') {
            return static::$cli = true;
        }

        return static::$cli = false;
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
     * @param mixed $key The key to look for. Pass false or null to
     *                   return the entire server array.
     * @param mixed $default Optional default value, which should be
     *                       returned if no element has been found
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
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function sanitize(string $key, $value)
    {
        // make sure $value is not null
        $value ??= '';

        switch ($key) {
            case 'SERVER_ADDR':
            case 'SERVER_NAME':
            case 'HTTP_HOST':
            case 'HTTP_X_FORWARDED_HOST':
                $value = strip_tags($value);
                $value = preg_replace('![^\w.:-]+!iu', '', $value);
                $value = trim($value, '-');
                $value = htmlspecialchars($value, ENT_COMPAT);
                break;
            case 'SERVER_PORT':
            case 'HTTP_X_FORWARDED_PORT':
                $value = (int)(preg_replace('![^0-9]+!', '', $value));
                break;
        }

        return $value;
    }

    /**
     * Returns the correct port number
     *
     * @param bool $forwarded
     * @return int
     */
    public static function port(bool $forwarded = false): int
    {
        // based on forwarded port
        if ($forwarded === true) {
            if ($port = static::get('HTTP_X_FORWARDED_PORT')) {
                return $port;
            }
        }

        // based on HTTP host
        $host = static::get('HTTP_HOST');
        if ($pos = strpos($host, ':')) {
            return (int)substr($host, $pos + 1);
        }

        // based on server port
        return static::get('SERVER_PORT');
    }

    /**
     * Checks for a https request
     *
     * @return bool
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
     * @param bool $forwarded
     * @return string
     */
    public static function host(bool $forwarded = false): string
    {
        $host = $forwarded === true ? static::get('HTTP_X_FORWARDED_HOST') : null;

        if (empty($host) === true) {
            $host = static::get('SERVER_NAME');
        }

        if (empty($host) === true) {
            $host = static::get('SERVER_ADDR');
        }

        return explode(':', $host)[0];
    }
}
