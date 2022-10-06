<?php

namespace Kirby\Http\Request;

/**
 * The Data Trait is being used in
 * Query, Files and Body classes to
 * provide unified get and data methods.
 * Especially the get method is a bit more
 * complex with the option to fetch single keys
 * or entire arrays.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Data
{
	/**
	 * Improved `var_dump` output
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * The data provider method has to be
	 * implemented by each class using this Trait
	 * and has to return an associative array
	 * for the get method
	 */
	abstract public function data(): array;

	/**
	 * The get method is the heart and soul of this
	 * Trait. You can use it to fetch a single value
	 * of the data array by key or multiple values by
	 * passing an array of keys.
	 */
	public function get(string|array $key, $default = null)
	{
		if (is_array($key) === true) {
			$result = [];
			foreach ($key as $k) {
				$result[$k] = $this->get($k);
			}
			return $result;
		}

		return $this->data()[$key] ?? $default;
	}

	/**
	 * Returns the data array.
	 * This is basically an alias for Data::data()
	 */
	public function toArray(): array
	{
		return $this->data();
	}

	/**
	 * Converts the data array to json
	 */
	public function toJson(): string
	{
		return json_encode($this->data());
	}
}
