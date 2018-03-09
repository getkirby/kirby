<?php

namespace Kirby\Cache;

/**
 * APC Cache
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class ApcCache extends Cache
{

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
        return apc_store($key, $this->value($value, $minutes), $this->expiration($minutes));
    }

    /**
     * Retrieve an item from the cache.
     *
     * @param  string  $key
     * @return mixed
     */
    public function retrieve(string $key)
    {
        return apc_fetch($key);
    }

    /**
     * Checks if the current key exists in cache
     *
     * @param  string  $key
     * @return boolean
     */
    public function exists(string $key): bool
    {
        return apc_exists($key);
    }

    /**
     * Remove an item from the cache
     *
     * @param  string  $key
     * @return boolean
     */
    public function remove(string $key): bool
    {
        return apc_delete($key);
    }

    /**
     * Flush the entire cache directory
     *
     * @return boolean
     */
    public function flush(): bool
    {
        return apc_clear_cache('user');
    }
}
