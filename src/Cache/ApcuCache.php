<?php

namespace Kirby\Cache;

use APCUIterator;

/**
 * APCu Cache Driver
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class ApcuCache extends Cache
{
    /**
     * Determines if an item exists in the cache
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return apcu_exists($this->key($key));
    }

    /**
     * Flushes the entire cache and returns
     * whether the operation was successful
     *
     * @return bool
     */
    public function flush(): bool
    {
        if (empty($this->options['prefix']) === false) {
            return apcu_delete(new APCUIterator('!^' . preg_quote($this->options['prefix']) . '!'));
        } else {
            return apcu_clear_cache();
        }
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
        return apcu_delete($this->key($key));
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
        return Value::fromJson(apcu_fetch($this->key($key)));
    }

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
        return apcu_store($this->key($key), $value->toJson(), $value->expires() ?? 0);
    }
}
