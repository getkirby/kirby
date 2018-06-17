<?php

namespace Kirby\Cache;

use Exception;
use Kirby\Http\Session as BaseSession;
use Kirby\Toolkit\A;

/**
* Session Cache Driver
*
* @package   Kirby Cache
* @author    Bastian Allgeier <bastian@getkirby.com>
* @link      http://getkirby.com
* @copyright Bastian Allgeier
* @license   MIT
*/
class SessionCache extends Cache
{

    /**
     * Make sure the session is started within the constructor
     */
    public function __construct()
    {
        BaseSession::start();
        if (!isset($_SESSION['_cache'])) {
            $_SESSION['_cache'] = [];
        }
    }

    /**
     * Write an item to the cache for a given number of minutes.
     *
     * <code>
     *    // Put an item in the cache for 15 minutes
     *    Cache::set('value', 'my value', 15);
     * </code>
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return void
     */
    public function set(string $key, $value, int $minutes = 0)
    {
        return $_SESSION['_cache'][$key] = $this->value($value, $minutes);
    }

    /**
     * Retrieve an item from the cache.
     *
     * @param  string  $key
     * @return object  CacheValue
     */
    public function retrieve(string $key)
    {
        return A::get($_SESSION['_cache'], $key);
    }

    /**
     * Remove an item from the cache
     *
     * @param  string  $key
     * @return boolean
     */
    public function remove(string $key): bool
    {
        unset($_SESSION['_cache'][$key]);
        return isset($_SESSION['_cache'][$key]);
    }

    /**
     * Flush the entire cache directory
     *
     * @return boolean
     */
    public function flush(): bool
    {
        $_SESSION['_cache'] = [];
        return true;
    }
}
