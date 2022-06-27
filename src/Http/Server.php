<?php

namespace Kirby\Http;

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
 * @deprecated 3.7.0 Use `Kirby\Http\Environment` instead
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
     * @return \Kirby\Http\Environment
     */
    public static function instance()
    {
        return new Environment([
            'cli'     => static::$cli,
            'allowed' => static::$hosts
        ]);
    }
}
