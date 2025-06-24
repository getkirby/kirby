<?php

namespace Kirby\Cache;

/**
 * Dummy Cache Driver (does not do any caching)
 *
 * @package   Kirby Cache
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class NullCache extends Cache
{
	/**
	 * Returns whether the cache is ready to
	 * store values
	 */
	public function enabled(): bool
	{
		return false;
	}

	/**
	 * Writes an item to the cache for a given number of minutes and
	 * returns whether the operation was successful
	 *
	 * ```php
	 * // put an item in the cache for 15 minutes
	 * $cache->set('value', 'my value', 15);
	 * ```
	 */
	public function set(string $key, $value, int $minutes = 0): bool
	{
		return true;
	}

	/**
	 * Internal method to retrieve the raw cache value;
	 * needs to return a Value object or null if not found
	 */
	public function retrieve(string $key): Value|null
	{
		return null;
	}

	/**
	 * Removes an item from the cache and returns
	 * whether the operation was successful
	 */
	public function remove(string $key): bool
	{
		return true;
	}

	/**
	 * Flushes the entire cache and returns
	 * whether the operation was successful
	 */
	public function flush(): bool
	{
		return true;
	}
}
