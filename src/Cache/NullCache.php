<?php

namespace Kirby\Cache;

/**
 * Dummy Cache Driver (does not do any caching)
 *
 * @package   Kirby Cache
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class NullCache extends Cache
{
    /**
     * Writes an item to the cache for a given number of minutes and
     * returns whether the operation was successful
     *
     * <code>
     *   // put an item in the cache for 15 minutes
     *   $cache->set('value', 'my value', 15);
     * </code>
     *
     * @param string $key
     * @param mixed $value
     * @param int $minutes
     * @return boolean
     */
    public function set(string $key, $value, int $minutes = 0): bool
    {
        return true;
    }

    /**
     * Internal method to retrieve the raw cache value;
     * needs to return a Value object or null if not found
     *
     * @param string $key
     * @return \Kirby\Cache\Value|null
     */
    public function retrieve(string $key)
    {
        return null;
    }

    /**
     * Removes an item from the cache and returns
     * whether the operation was successful
     *
     * @param string $key
     * @return boolean
     */
    public function remove(string $key): bool
    {
        return true;
    }

    /**
     * Flushes the entire cache and returns
     * whether the operation was successful
     *
     * @return boolean
     */
    public function flush(): bool
    {
        return true;
    }
}
