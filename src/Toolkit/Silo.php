<?php

namespace Kirby\Toolkit;

/**
 * The Silo class is a core class to handle
 * setting, getting and removing static data of
 * a singleton.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Silo
{
	public static array $data = [];

	/**
	 * Setter for new data
	 */
	public static function set(string|array $key, $value = null): array
	{
		if (is_array($key) === true) {
			return static::$data = [...static::$data, ...$key];
		}

		static::$data[$key] = $value;
		return static::$data;
	}

	public static function get(string|array|null $key = null, $default = null)
	{
		if ($key === null) {
			return static::$data;
		}

		return A::get(static::$data, $key, $default);
	}

	/**
	 * Removes an item from the data array
	 */
	public static function remove(string|null $key = null): array
	{
		// reset the entire array
		if ($key === null) {
			return static::$data = [];
		}

		// unset a single key
		unset(static::$data[$key]);

		// return the array without the removed key
		return static::$data;
	}
}
