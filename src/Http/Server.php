<?php

namespace Kirby\Http;

use Kirby\Toolkit\A;

/**
 * A set of methods that make it more convenient to get variables
 * from the global server array
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Server
{
    public const HOST_FROM_SERVER = 1;
    public const HOST_FROM_HEADER = 2;
    public const HOST_ALLOW_EMPTY = 4;

    /**
     * Cache for the cli status
     *
     * @var bool|null
     */
    public static $cli;

    /**
     * List of trusted hosts
     *
     * @var array
     */
    public static $hosts = [];

    /**
     * Returns the server's IP address
     *
     * @return string
     */
    public static function address(): string
    {
        return static::get('SERVER_ADDR', '');
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
     * Returns the correct host
     *
     * @param bool $forwarded Deprecated. Todo: remove in 3.7.0
     * @return string
     */
    public static function host(bool $forwarded = false): string
    {
        $hosts[] = static::get('SERVER_NAME');
        $hosts[] = static::get('SERVER_ADDR');

        // insecure host parameters are only allowed when hosts
        // are validated against set of host patterns
        if (empty(static::$hosts) === false) {
            $hosts[] = static::get('HTTP_HOST');
            $hosts[] = static::get('HTTP_X_FORWARDED_HOST');
        }

        // remove empty hosts
        $hosts = array_filter($hosts);

        foreach ($hosts as $host) {
            if (static::isAllowedHost($host) === true) {
                return explode(':', $host)[0];
            }
        }

        return '';
    }

    /**
     * Setter and getter for the the static $hosts property
     *
     * $hosts = null                     -> return all defined hosts
     * $hosts = Server::HOST_FROM_SERVER -> []
     * $hosts = Server::HOST_FROM_HEADER -> ['*']
     * $hosts = array                    -> [array of trusted hosts]
     * $hosts = string                   -> [single trusted host]
     *
     * @param string|array|int|null $hosts
     * @return array
     */
    public static function hosts($hosts = null): array
    {
        if ($hosts === null) {
            return static::$hosts;
        }

        if (is_int($hosts) && $hosts & static::HOST_FROM_SERVER) {
            return static::$hosts = [];
        }

        if (is_int($hosts) && $hosts & static::HOST_FROM_HEADER) {
            return static::$hosts = ['*'];
        }

        // make sure hosts are always an array
        $hosts = A::wrap($hosts);

        // return unique hosts
        return static::$hosts = array_unique($hosts);
    }

    /**
     * Checks for a https request
     *
     * @return bool
     */
    public static function https(): bool
    {
        $https = $_SERVER['HTTPS'] ?? null;
        $off   = ['off', null, '', 0, '0', false, 'false', -1, '-1'];

        // check for various options to send a negative HTTPS header
        if (in_array($https, $off, true) === false) {
            return true;
        }

        // check for the port
        if (static::port() === 443) {
            return true;
        }

        return false;
    }

    /**
     * Checks for allowed host names
     *
     * @param string $host
     * @return bool
     */
    public static function isAllowedHost(string $host): bool
    {
        if (empty(static::$hosts) === true) {
            return true;
        }

        foreach (static::$hosts as $pattern) {
            if (empty($pattern) === true) {
                continue;
            }

            if (fnmatch($pattern, $host) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the server is behind a
     * proxy server.
     *
     * @return bool
     */
    public static function isBehindProxy(): bool
    {
        return empty($_SERVER['HTTP_X_FORWARDED_HOST']) === false;
    }

    /**
     * Returns the correct port number
     *
     * @param bool $forwarded Deprecated. Todo: remove in 3.7.0
     * @return int
     */
    public static function port(bool $forwarded = false): int
    {
        $port = null;

        // handle reverse proxy setups
        if (static::isBehindProxy() === true) {
            // based on forwarded port
            $port = static::get('HTTP_X_FORWARDED_PORT');

            // based on the forwarded host
            if (empty($port) === true) {
                $port = (int)parse_url(static::get('HTTP_X_FORWARDED_HOST'), PHP_URL_PORT);
            }

            // based on the forwarded proto
            if (empty($port) === true) {
                if (in_array($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null, ['https', 'https, http']) === true) {
                    $port = 443;
                }
            }
        }

        // based on the host
        if (empty($port) === true) {
            $port = (int)parse_url(static::get('HTTP_HOST'), PHP_URL_PORT);
        }

        // based on server port
        if (empty($port) === true) {
            $port = static::get('SERVER_PORT');
        }

        return $port ?? 0;
    }

    /**
     * Returns an array with path and query
     * from the REQUEST_URI
     *
     * @return array
     */
    public static function requestUri(): array
    {
        $uri = static::get('REQUEST_URI', '');
        $uri = parse_url($uri);

        return [
            'path'  => $uri['path']  ?? null,
            'query' => $uri['query'] ?? null,
        ];
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
        switch ($key) {
            case 'SERVER_ADDR':
            case 'SERVER_NAME':
            case 'HTTP_HOST':
            case 'HTTP_X_FORWARDED_HOST':
                $value ??= '';
                $value = strtolower($value);
                $value = strip_tags($value);
                $value = basename($value);
                $value = preg_replace('![^\w.:-]+!iu', '', $value);
                $value = htmlspecialchars($value, ENT_COMPAT);
                $value = trim($value, '-');
                $value = trim($value, '.');
                break;
            case 'SERVER_PORT':
            case 'HTTP_X_FORWARDED_PORT':
                $value ??= '';
                $value = (int)(preg_replace('![^0-9]+!', '', $value));
                break;
            }

        return $value;
    }

    /**
     * Returns the path to the php script
     * within the document root without the
     * filename of the script.
     *
     * i.e. /subfolder/index.php -> subfolder
     *
     * This can be used to build the base url
     * for subfolder installations
     *
     * @return string
     */
    public static function scriptPath(): string
    {
        if (static::cli() === true) {
            return '';
        }

        $path = $_SERVER['SCRIPT_NAME'] ?? '';
        // replace Windows backslashes
        $path = str_replace('\\', '/', $path);
        // remove the script
        $path = dirname($path);
        // replace those fucking backslashes again
        $path = str_replace('\\', '/', $path);
        // remove the leading and trailing slashes
        $path = trim($path, '/');

        // top-level scripts don't have a path
        // and dirname() will return '.'
        if ($path === '.') {
            $path = '';
        }

        return $path;
    }
}
