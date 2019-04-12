<?php

namespace Kirby\Cache;

use APCUIterator;

/**
 * APCu Cache Driver
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class ApcuCache extends Cache
{

    /**
     * Checks if the current key exists in cache
     *
     * @param  string  $key
     * @return boolean
     */
    public function exists(string $key): bool
    {
        return apcu_exists($this->key($key));
    }

    /**
     * Flush the entire cache directory
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
     * Remove an item from the cache
     *
     * @param  string  $key
     * @return boolean
     */
    public function remove(string $key): bool
    {
        return apcu_delete($this->key($key));
    }

    /**
     * Retrieve an item from the cache.
     *
     * @param  string  $key
     * @return mixed
     */
    public function retrieve(string $key)
    {
        return Value::fromJson(apcu_fetch($this->key($key)));
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
        return apcu_store($this->key($key), $this->value($value, $minutes)->toJson(), $this->expiration($minutes));
    }
}
