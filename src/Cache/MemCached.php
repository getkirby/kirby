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
     * Sets all parameters which are needed to connect to Memcached
     *
     * @param array $options 'host'   (default: localhost)
     *                       'port'   (default: 11211)
     *                       'prefix' (default: null)
     */
    public function __construct(array $options = [])
    {
        $defaults = [
            'host'    => 'localhost',
            'port'    => 11211,
            'prefix'  => null,
        ];

        parent::__construct(array_merge($defaults, $options));

        $this->connection = new \Memcached();
        $this->connection->addServer($this->options['host'], $this->options['port']);
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
        return $this->connection->set($this->key($key), (new Value($value, $minutes))->toJson(), $this->expiration($minutes));
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
        return Value::fromJson($this->connection->get($this->key($key)));
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
        return $this->connection->delete($this->key($key));
    }

    /**
     * Flushes the entire cache and returns
     * whether the operation was successful;
     * WARNING: Memcached only supports flushing the whole cache at once!
     *
     * @return boolean
     */
    public function flush(): bool
    {
        return $this->connection->flush();
    }
}
