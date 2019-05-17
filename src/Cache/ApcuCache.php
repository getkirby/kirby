<?php

namespace Kirby\Cache;

use APCUIterator;

/**
 * APCu Cache Driver
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class ApcuCache extends Cache
{

    /**
     * Determines if an item exists in the cache
     *
     * @param string $key
     * @return boolean
     */
    public function exists(string $key): bool
    {
        return apcu_exists($this->key($key));
    }

    /**
     * Flushes the entire cache and returns
     * whether the operation was successful
     *
     * @return boolean
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
     * @return boolean
     */
    public function remove(string $key): bool
    {
        return apcu_delete($this->key($key));
    }

    /**
     * Internal method to retrieve the raw cache value;
     * needs to return a Value object or null if not found
     *
     * @param string $key
     * @return Kirby\Cache\Value|null
     */
    public function retrieve(string $key)
    {
        return Value::fromJson(apcu_fetch($this->key($key)));
    }

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
        return apcu_store($this->key($key), (new Value($value, $minutes))->toJson(), $this->expiration($minutes));
    }
}
