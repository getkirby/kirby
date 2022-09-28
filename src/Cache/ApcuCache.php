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
	 * Returns whether the cache is ready to
	 * store values
	 */
	public function enabled(): bool
	{
		return apcu_enabled();
	}

	/**
	 * Determines if an item exists in the cache
	 */
	public function exists(string $key): bool
	{
		return apcu_exists($this->key($key));
	}

	/**
	 * Flushes the entire cache and returns
	 * whether the operation was successful
	 */
	public function flush(): bool
	{
		if (empty($this->options['prefix']) === false) {
			return apcu_delete(new APCUIterator('!^' . preg_quote($this->options['prefix']) . '!'));
		}

		return apcu_clear_cache();
	}

	/**
	 * Removes an item from the cache and returns
	 * whether the operation was successful
	 */
	public function remove(string $key): bool
	{
		return apcu_delete($this->key($key));
	}

	/**
	 * Internal method to retrieve the raw cache value;
	 * needs to return a Value object or null if not found
	 */
	public function retrieve(string $key): Value|null
	{
		$value = apcu_fetch($this->key($key));
		return Value::fromJson($value);
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
		return apcu_store($key, $value, $expires);
	}
}
