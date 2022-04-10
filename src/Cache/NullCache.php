<?php

namespace Kirby\Cache;

/**
 * Dummy Cache Driver (does not do any caching)
 *
 * @package   Kirby Cache
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class NullCache extends Cache
{
    /**
     * Internal method to store the raw cache value;
     * returns whether the operation was successful
     *
     * @internal
     * @param string $key
     * @param \Kirby\Cache\Value $value
     * @return bool
     */
    public function store(string $key, Value $value): bool
    {
        return true;
    }

    /**
     * Internal method to retrieve the raw cache value;
     * needs to return a Value object or null if not found
     *
     * @internal
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
     * @return bool
     */
    public function remove(string $key): bool
    {
        return true;
    }

    /**
     * Flushes the entire cache and returns
     * whether the operation was successful
     *
     * @return bool
     */
    public function flush(): bool
    {
        return true;
    }
}
