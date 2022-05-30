<?php

namespace Kirby\Http;

use Kirby\Toolkit\A;
use Kirby\Toolkit\Facade;

/**
 * A set of methods that make it more convenient to get variables
 * from the global server array
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @deprecated 3.7.0 Use `Cms\Environment` instead
 * @todo Remove in 3.8.0
 */
class Server extends Facade
{
    public const HOST_FROM_SERVER = 1;
    public const HOST_FROM_HEADER = 2;
    public const HOST_ALLOW_EMPTY = 4;

    public static $cli;
    public static $hosts;

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
     * @return \Kirby\Http\Environment
     */
    public static function instance()
    {
        return new Environment([
            'cli'     => static::$cli,
            'allowed' => static::$hosts
        ]);
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
}
