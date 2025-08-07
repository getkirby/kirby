<?php

namespace Kirby\Cache;

use Kirby\Cms\Helpers;
use Redis;
use Throwable;

/**
 * Redis Cache Driver
 *
 * @package   Kirby Cache
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class RedisCache extends Cache
{
	/**
	 * Store for the redis connection
	 */
	protected Redis $connection;

	/**
	 * Sets all parameters which are needed to connect to Redis
	 *
	 * @param array $options 'host'   (default: 127.0.0.1)
	 *                       'port'   (default: 6379)
	 */
	public function __construct(array $options = [])
	{
		$options = [
			'host' => '127.0.0.1',
			'port' => 6379,
			...$options
		];

		parent::__construct($options);

		// available options for the redis driver
		$allowed = [
			'host',
			'port',
			'readTimeout',
			'connectTimeout',
			'persistent',
			'auth',
			'ssl',
			'retryInterval',
			'backoff'
		];

		// filters only redis supported keys
		$redisOptions = array_intersect_key($options, array_flip($allowed));

		// creates redis connection
		$this->connection = new Redis($redisOptions);

		// sets the prefix if defined
		if ($prefix = $options['prefix'] ?? null) {
			$this->connection->setOption(Redis::OPT_PREFIX, rtrim($prefix, '/') . '/');
		}

		// selects the database if defined
		$database = $options['database'] ?? null;
		if ($database !== null) {
			$this->connection->select($database);
		}
	}

	/**
	 * Returns the database number
	 */
	public function databaseNum(): int
	{
		return $this->connection->getDbNum();
	}

	/**
	 * Returns whether the cache is ready to store values
	 */
	public function enabled(): bool
	{
		try {
			return Helpers::handleErrors(
				fn () => $this->connection->ping(),
				fn (int $errno, string $errstr) => true,
				fn () => false
			);
		} catch (Throwable) {
			return false;
		}
	}

	/**
	 * Determines if an item exists in the cache
	 */
	public function exists(string $key): bool
	{
		return $this->connection->exists($this->key($key)) !== 0;
	}

	/**
	 * Removes keys from the database
	 * and returns whether the operation was successful
	 */
	public function flush(): bool
	{
		return $this->connection->flushDB();
	}

	/**
	 * The key is not modified, because the prefix is added by the redis driver itself
	 */
	protected function key(string $key): string
	{
		return $key;
	}

	/**
	 * Removes an item from the cache
	 * and returns whether the operation was successful
	 */
	public function remove(string $key): bool
	{
		return $this->connection->del($this->key($key));
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
	 * Writes an item to the cache for a given number of minutes
	 * and returns whether the operation was successful
	 *
	 * ```php
	 * // put an item in the cache for 15 minutes
	 * $cache->set('value', 'my value', 15);
	 * ```
	 */
	public function set(string $key, $value, int $minutes = 0): bool
	{
		$key   = $this->key($key);
		$value = (new Value($value, $minutes))->toJson();

		if ($minutes > 0) {
			return $this->connection->setex($key, $minutes * 60, $value);
		}

		return $this->connection->set($key, $value);
	}
}
