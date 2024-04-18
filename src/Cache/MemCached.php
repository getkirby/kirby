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
	 * Store for the memcache connection
	 */
	protected MemcachedExt $connection;

	/**
	 * Stores whether the connection was successful
	 */
	protected bool $enabled;

	/**
	 * Sets all parameters which are needed to connect to Memcached
	 *
	 * @param array $options 'host'   (default: localhost)
	 *                       'port'   (default: 11211)
	 *                       'prefix' (default: null)
	 */
	public function __construct(array $options = [])
	{
		parent::__construct([
			'host'    => 'localhost',
			'port'    => 11211,
			'prefix'  => null,
			...$options
		]);

		$this->connection = new MemcachedExt();
		$this->enabled = $this->connection->addServer(
			$this->options['host'],
			$this->options['port']
		);
	}

	/**
	 * Returns whether the cache is ready to
	 * store values
	 */
	public function enabled(): bool
	{
		return $this->enabled;
	}

	/**
	 * Writes an item to the cache for a given number of minutes and
	 * returns whether the operation was successful
	 *
	 * <code>
	 *   // put an item in the cache for 15 minutes
	 *   $cache->set('value', 'my value', 15);
	 * </code>
	 */
	public function set(string $key, $value, int $minutes = 0): bool
	{
		$key     = $this->key($key);
		$value   = (new Value($value, $minutes))->toJson();
		$expires = $this->expiration($minutes);
		return $this->connection->set($key, $value, $expires);
	}

	/**
	 * Internal method to retrieve the raw cache value;
	 * needs to return a Value object or null if not found
	 */
	public function retrieve(string $key): Value|null
	{
		$value = $this->connection->get($this->key($key));
		return Value::fromJson($value);
	}

	/**
	 * Removes an item from the cache and returns
	 * whether the operation was successful
	 */
	public function remove(string $key): bool
	{
		return $this->connection->delete($this->key($key));
	}

	/**
	 * Flushes the entire cache and returns
	 * whether the operation was successful;
	 * WARNING: Memcached only supports flushing the whole cache at once!
	 */
	public function flush(): bool
	{
		return $this->connection->flush();
	}
}
