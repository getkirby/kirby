<?php

namespace Kirby\Cache;

use Memcached as MemcachedExt;

/**
 * Memcached Driver
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class MemCached extends Cache
{
    /**
     * store for the memcache connection
     * @var \Memcached
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

        $this->connection = new MemcachedExt();
        $this->connection->addServer($this->options['host'], $this->options['port']);
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
        return $this->connection->set($this->key($key), $value->toJson(), $value->expires() ?? 0);
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
        return Value::fromJson($this->connection->get($this->key($key)));
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
        return $this->connection->delete($this->key($key));
    }

    /**
     * Flushes the entire cache and returns
     * whether the operation was successful;
     * WARNING: Memcached only supports flushing the whole cache at once!
     *
     * @return bool
     */
    public function flush(): bool
    {
        return $this->connection->flush();
    }
}
