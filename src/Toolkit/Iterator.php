<?php

namespace Kirby\Toolkit;

use ArrayIterator;
use Countable;
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
 *
 * @template TKey of array-key
 * @template TValue
 * @implements \IteratorAggregate<TKey, TValue>
 */
class Iterator implements Countable, IteratorAggregate
{
	/**
	 * @var array<TKey, TValue>
	 */
	public array $data = [];

	public function __construct(array $data = [])
	{
		$this->data = $data;
	}

	/**
	 * Get an iterator for the items.
	 * @return \ArrayIterator<TKey, TValue>
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->data);
	}

	/**
	 * Returns the current key
	 */
	public function key(): int|string|null
	{
		return key($this->data);
	}

	/**
	 * Returns an array of all keys
	 */
	public function keys(): array
	{
		return array_keys($this->data);
	}

	/**
	 * Returns the current element
	 * @return TValue
	 */
	public function current(): mixed
	{
		return current($this->data);
	}

	/**
	 * Moves the cursor to the previous element
	 * and returns it
	 * @return TValue
	 */
	public function prev(): mixed
	{
		return prev($this->data);
	}

	/**
	 * Moves the cursor to the next element
	 * and returns it
	 * @return TValue
	 */
	public function next(): mixed
	{
		return next($this->data);
	}

	/**
	 * Moves the cursor to the first element
	 */
	public function rewind(): void
	{
		reset($this->data);
	}

	/**
	 * Checks if the current element is valid
	 */
	public function valid(): bool
	{
		return $this->current() !== false;
	}

	/**
	 * Counts all elements
	 */
	public function count(): int
	{
		return count($this->data);
	}

	/**
	 * Tries to find the index number for the given element
	 *
	 * @param TValue $needle the element to search for
	 * @return int|false the index (int) of the element or false
	 */
	public function indexOf(mixed $needle): int|false
	{
		return array_search($needle, array_values($this->data));
	}

	/**
	 * Tries to find the key for the given element
	 *
	 * @param TValue $needle the element to search for
	 * @return int|string|false the name of the key or false
	 */
	public function keyOf(mixed $needle): int|string|false
	{
		return array_search($needle, $this->data);
	}

	/**
	 * Checks by key if an element is included
	 * @param TKey $key
	 */
	public function has(mixed $key): bool
	{
		return isset($this->data[$key]) === true;
	}

	/**
	 * Checks if the current key is set
	 * @param TKey $key
	 */
	public function __isset(mixed $key): bool
	{
		return $this->has($key);
	}

	/**
	 * Simplified var_dump output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->data;
	}
}
