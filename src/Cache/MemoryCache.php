<?php

namespace Kirby\Cache;

/**
 * Memory Cache Driver (cache in memory for current request only)
 *
 * @package   Kirby Cache
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class MemoryCache extends Cache
{
    /**
     * Cache data
     * @var array
     */
    protected $store = [];

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
        $this->store[$key] = $value;
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
        return $this->store[$key] ?? null;
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
        if (isset($this->store[$key])) {
            unset($this->store[$key]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Flushes the entire cache and returns
     * whether the operation was successful
     *
     * @return bool
     */
    public function flush(): bool
    {
        $this->store = [];
        return true;
    }
}
