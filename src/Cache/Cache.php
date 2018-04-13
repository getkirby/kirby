<?php

namespace Kirby\Cache;

/**
 * Cache foundation
 * This class doesn't do anything
 * and is perfect as foundation for
 * other cache drivers and to be used
 * when the cache is disabled
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Cache
{

    /**
     * stores all options for the driver
     * @var array
     */
    protected $options = [];

    /**
     * Set all parameters which are needed to connect to the cache storage
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
    }

    /**
     * Write an item to the cache for a given number of minutes.
     *
     * <code>
     *   // Put an item in the cache for 15 minutes
     *   Cache::set('value', 'my value', 15);
     * </code>
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return void
     */
    public function set(string $key, $value, int $minutes = 0)
    {
        return null;
    }

    /**
     * Private method to retrieve the cache value
     * This needs to be defined by the driver
     *
     * @param  string $key
     * @return mixed
     */
    public function retrieve(string $key)
    {
        return null;
    }

    /**
     * Get an item from the cache.
     *
     * <code>
     *   // Get an item from the cache driver
     *   $value = Cache::get('value');
     *
     *   // Return a default value if the requested item isn't cached
     *   $value = Cache::get('value', 'default value');
     * </code>
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // get the Value
        $value = $this->retrieve($key);

        // check for a valid cache value
        if (!is_a($value, Value::class)) {
            return $default;
        }

        // remove the item if it is expired
        if (time() >= $value->expires()) {
            $this->remove($key);
            return $default;
        }

        // get the pure value
        $cache = $value->value();

        // return the cache value or the default
        return $cache ?? $default;
    }

    /**
     * Calculates the expiration timestamp
     *
     * @param  int $minutes
     * @return int
     */
    protected function expiration(int $minutes = 0): int
    {
        // keep forever if minutes are not defined
        if ($minutes === 0) {
            $minutes = 2628000;
        }

        // calculate the time
        return time() + ($minutes * 60);
    }

    /**
     * Checks when an item in the cache expires
     *
     * @param  string $key
     * @return mixed
     */
    public function expires(string $key)
    {
        // get the Value object
        $value = $this->retrieve($key);

        // check for a valid Value object
        if (!is_a($value, Value::class)) {
            return false;
        }

        // return the expires timestamp
        return $value->expires();
    }

    /**
     * Checks if an item in the cache is expired
     *
     * @param  string   $key
     * @return boolean
     */
    public function expired(string $key): bool
    {
        return $this->expires($key) <= time();
    }

    /**
     * Checks when the cache has been created
     *
     * @param  string $key
     * @return mixed
     */
    public function created(string $key)
    {
        // get the Value object
        $value = $this->retrieve($key);

        // check for a valid Value object
        if (!is_a($value, Value::class)) {
            return false;
        }

        // return the expires timestamp
        return $value->created();
    }

    /**
     * Alternate version for Cache::created($key)
     *
     * @param  string $key
     * @return mixed
     */
    public function modified(string $key)
    {
        return static::created($key);
    }

    /**
     * Returns Value object
     *
     * @param  mixed  $value    The value, which should be cached
     * @param  int    $minutes  The number of minutes before expiration
     * @return Value
     */
    protected function value($value, int $minutes): Value
    {
        return new Value($value, $minutes);
    }

    /**
     * Determine if an item exists in the cache.
     *
     * @param  string  $key
     * @return boolean
     */
    public function exists(string $key): bool
    {
        return !$this->expired($key);
    }

    /**
     * Remove an item from the cache
     *
     * @param  string $key
     * @return boolean
     */
    public function remove(string $key): bool
    {
        return true;
    }

    /**
     * Flush the entire cache
     *
     * @return boolean
     */
    public function flush(): bool
    {
        return true;
    }

    /**
     * Returns all passed cache options
     *
     * @return array
     */
    public function options(): array
    {
        return $this->options;
    }
}
