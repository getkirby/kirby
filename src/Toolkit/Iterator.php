<?php

namespace Kirby\Toolkit;

use ArrayIterator;
use Countable;
use Iterator as PhpIterator;
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
	 * Returns an iterator for the elements
	 * @return \ArrayIterator<TKey, TValue>
	 */
	public function getIterator(): PhpIterator
	{
		return new ArrayIterator($this->data);
	}

	/**
	 * Returns the current key
	 * @deprecated
	 * @todo Remove in v6
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
	 * @deprecated
	 * @todo Remove in v6
	 * @return TValue
	 */
	public function current(): mixed
	{
		return current($this->data);
	}

	/**
	 * Moves the cursor to the previous element
	 * and returns it
	 * @deprecated
	 * @todo Remove in v6
	 * @return TValue
	 */
	public function prev(): mixed
	{
		return prev($this->data);
	}

	/**
	 * Moves the cursor to the next element
	 * and returns it
	 * @deprecated
	 * @todo Remove in v6
	 * @return TValue
	 */
	public function next(): mixed
	{
		return next($this->data);
	}

	/**
	 * Moves the cursor to the first element
	 * @deprecated
	 * @todo Remove in v6
	 */
	public function rewind(): void
	{
		reset($this->data);
	}

	/**
	 * Checks if the current element is valid
	 * @deprecated
	 * @todo Remove in v6
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
		return array_key_exists($key, $this->data) === true;
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
