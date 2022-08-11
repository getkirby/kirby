<?php

namespace Kirby\Toolkit;

use ArrayIterator;
use IteratorAggregate;

/**
 * Extended version of PHP's iterator
 * class that builds the foundation of our
 * Collection class.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Iterator implements IteratorAggregate
{
	/**
	 * The data array
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Constructor
	 *
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		$this->data = $data;
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->data);
	}

	/**
	 * Returns the current key
	 *
	 * @return string
	 */
	public function key()
	{
		return key($this->data);
	}

	/**
	 * Returns an array of all keys
	 *
	 * @return array
	 */
	public function keys(): array
	{
		return array_keys($this->data);
	}

	/**
	 * Returns the current element
	 *
	 * @return mixed
	 */
	public function current()
	{
		return current($this->data);
	}

	/**
	 * Moves the cursor to the previous element
	 * and returns it
	 *
	 * @return mixed
	 */
	public function prev()
	{
		return prev($this->data);
	}

	/**
	 * Moves the cursor to the next element
	 * and returns it
	 *
	 * @return mixed
	 */
	public function next()
	{
		return next($this->data);
	}

	/**
	 * Moves the cursor to the first element
	 */
	public function rewind()
	{
		reset($this->data);
	}

	/**
	 * Checks if the current element is valid
	 *
	 * @return bool
	 */
	public function valid(): bool
	{
		return $this->current() !== false;
	}

	/**
	 * Counts all elements
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->data);
	}

	/**
	 * Tries to find the index number for the given element
	 *
	 * @param mixed $needle the element to search for
	 * @return int|false the index (int) of the element or false
	 */
	public function indexOf($needle)
	{
		return array_search($needle, array_values($this->data));
	}

	/**
	 * Tries to find the key for the given element
	 *
	 * @param mixed $needle the element to search for
	 * @return string|false the name of the key or false
	 */
	public function keyOf($needle)
	{
		return array_search($needle, $this->data);
	}

	/**
	 * Checks by key if an element is included
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public function has($key): bool
	{
		return isset($this->data[$key]);
	}

	/**
	 * Checks if the current key is set
	 *
	 * @param mixed $key the key to check
	 * @return bool
	 */
	public function __isset($key): bool
	{
		return $this->has($key);
	}

	/**
	 * Simplified var_dump output
	 *
	 * @return array
	 */
	public function __debugInfo(): array
	{
		return $this->data;
	}
}
