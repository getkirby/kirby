<?php

namespace Kirby\Cache;

use Closure;

/**
 * Cache foundation
 * This abstract class is used as
 * foundation for other cache drivers
 * by extending it
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Cache
{
	/**
	 * Stores all options for the driver
	 */
	protected array $options = [];

	/**
	 * Sets all parameters which are needed to connect to the cache storage
	 */
	public function __construct(array $options = [])
	{
		$this->options = $options;
	}

	/**
	 * Checks when the cache has been created;
	 * returns the creation timestamp on success
	 * and false if the item does not exist
	 */
	public function created(string $key): int|false
	{
		// get the Value object
		$value = $this->retrieve($key);

		// check for a valid Value object
		if ($value instanceof Value === false) {
			return false;
		}

		// return the expires timestamp
		return $value->created();
	}

	/**
	 * Returns whether the cache is ready to
	 * store values
	 */
	public function enabled(): bool
	{
		// TODO: Make this method abstract in a future
		// release to ensure that cache drivers override it;
		// until then, we assume that the cache is enabled
		return true;
	}

	/**
	 * Determines if an item exists in the cache
	 */
	public function exists(string $key): bool
	{
		return $this->expired($key) === false;
	}


	/**
	 * Calculates the expiration timestamp
	 */
	protected function expiration(int $minutes = 0): int
	{
		// 0 = keep forever
		if ($minutes === 0) {
			return 0;
		}

		// calculate the time
		return time() + ($minutes * 60);
	}

	/**
	 * Checks when an item in the cache expires;
	 * returns the expiry timestamp on success, null if the
	 * item never expires and false if the item does not exist
	 */
	public function expires(string $key): int|false|null
	{
		// get the Value object
		$value = $this->retrieve($key);

		// check for a valid Value object
		if ($value instanceof Value === false) {
			return false;
		}

		// return the expires timestamp
		return $value->expires();
	}

	/**
	 * Checks if an item in the cache is expired
	 */
	public function expired(string $key): bool
	{
		$expires = $this->expires($key);

		if ($expires === null) {
			return false;
		}

		if (is_int($expires) === false) {
			return true;
		}

		return time() >= $expires;
	}

	/**
	 * Flushes the entire cache and returns
	 * whether the operation was successful;
	 * this needs to be defined by the driver
	 */
	abstract public function flush(): bool;

	/**
	 * Gets an item from the cache
	 *
	 * <code>
	 *   // get an item from the cache driver
	 *   $value = $cache->get('value');
	 *
	 *   // return a default value if the requested item isn't cached
	 *   $value = $cache->get('value', 'default value');
	 * </code>
	 */
	public function get(string $key, $default = null)
	{
		// get the Value
		$value = $this->retrieve($key);

		// check for a valid cache value
		if ($value instanceof Value === false) {
			return $default;
		}

		// remove the item if it is expired
		if ($value->expires() > 0 && time() >= $value->expires()) {
			$this->remove($key);
			return $default;
		}

		// return the pure value
		return $value->value();
	}

	/**
	 * Returns a value by either getting it from the cache
	 * or via the callback function which then is stored in
	 * the cache for future retrieval. This method cannot be
	 * used for `null` as value to be cached.
	 * @since 3.8.0
	 */
	public function getOrSet(
		string $key,
		Closure $result,
		int $minutes = 0
	) {
		$value  = $this->get($key);
		$result = $value ?? $result();

		if ($value === null) {
			$this->set($key, $result, $minutes);
		}

		return $result;
	}

	/**
	 * Adds the prefix to the key if given
	 */
	protected function key(string $key): string
	{
		if (empty($this->options['prefix']) === false) {
			$key = $this->options['prefix'] . '/' . $key;
		}

		return $key;
	}

	/**
	 * Alternate version for Cache::created($key)
	 */
	public function modified(string $key): int|false
	{
		return static::created($key);
	}

	/**
	 * Returns all passed cache options
	 */
	public function options(): array
	{
		return $this->options;
	}

	/**
	 * Removes an item from the cache and returns
	 * whether the operation was successful;
	 * this needs to be defined by the driver
	 */
	abstract public function remove(string $key): bool;

	/**
	 * Internal method to retrieve the raw cache value;
	 * needs to return a Value object or null if not found;
	 * this needs to be defined by the driver
	 */
	abstract public function retrieve(string $key): Value|null;

	/**
	 * Writes an item to the cache for a given number of minutes and
	 * returns whether the operation was successful;
	 * this needs to be defined by the driver
	 *
	 * <code>
	 *   // put an item in the cache for 15 minutes
	 *   $cache->set('value', 'my value', 15);
	 * </code>
	 */
	abstract public function set(string $key, $value, int $minutes = 0): bool;
}
