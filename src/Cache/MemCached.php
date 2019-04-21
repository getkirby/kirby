<?php

namespace Kirby\Cache;

/**
 * Memcached Driver
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class MemCached extends Cache
{

    /**
     * store for the memache connection
     * @var Memcached
     */
    protected $connection;

    /**
     * Set all parameters which are needed for the memcache client
     * see defaults for available parameters
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $defaults = [
            'host'    => 'localhost',
            'port'    => 11211,
            'prefix'  => null,
        ];

        parent::__construct(array_merge($defaults, $params));

        $this->connection = new \Memcached();
        $this->connection->addServer($this->options['host'], $this->options['port']);
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
     * @return boolean
     */
    public function set(string $key, $value, int $minutes = 0): bool
    {
        return $this->connection->set($this->key($key), (new Value($value, $minutes))->toJson(), $this->expiration($minutes));
    }

    /**
     * Internal method to retrieve the raw cache value;
     * needs to return a Value object or null if not found
     *
     * @param  string $key
     * @return mixed
     */
    public function retrieve(string $key): ?Value
    {
        return Value::fromJson($this->connection->get($this->key($key)));
    }

    /**
     * Remove an item from the cache
     *
     * @param  string  $key
     * @return boolean
     */
    public function remove(string $key): bool
    {
        return $this->connection->delete($this->key($key));
    }

    /**
     * Flush the entire cache directory
     *
     * @return boolean
     */
    public function flush(): bool
    {
        return $this->connection->flush();
    }
}
