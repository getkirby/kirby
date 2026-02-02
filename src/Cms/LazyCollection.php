<?php

namespace Kirby\Cms;

use Closure;
use Iterator;
use Kirby\Exception\LogicException;

/**
 * The LazyCollection class is a variant of the CMS
 * Collection that is only initialized with keys for
 * each collection element or without any data.
 * Collection elements and their values (= objects)
 * are loaded and initialized lazily when they are
 * first used.
 *
 * You can use LazyCollection in two ways:
 * 1. Initialize with keys only (values are `null`),
 *    define `hydrateElement` method that initializes
 *    an element dynamically.
 * 2. Option 1, but also don't initialize any keys,
 *    set `$initialized` prop to `false` and define
 *    `initialize` method that defines which keys
 *    are available.
 *
 * @package   Kirby Cms
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @template TValue
 * @extends \Kirby\Cms\Collection<TValue>
 */
abstract class LazyCollection extends Collection
{
	/**
	 * Flag that tells whether hydration has been
	 * completed for all collection elements;
	 * this is used to increase performance
	 */
	protected bool $hydrated = false;

	/**
	 * Flag that tells whether all possible collection
	 * items have been loaded (only relevant in lazy
	 * initialization mode)
	 */
	protected bool $initialized = true;

	/**
	 * Temporary auto-hydration whenever a collection
	 * method is called; some methods may not need raw
	 * access to all collection data, so performance
	 * will be improved if methods call initialization
	 * or hydration themselves only if they need it
	 * @deprecated
	 * @todo Remove this in v6
	 */
	public function __call(string $key, $arguments)
	{
		$this->hydrate();
		return parent::__call($key, $arguments);
	}

	/**
	 * Low-level getter for elements
	 *
	 * @return TValue|null
	 */
	public function __get(string $key)
	{
		$element = parent::__get($key);

		// `$element === null` could mean "element does not exist"
		// or "element found but not hydrated"
		if (
			$element === null &&
			(array_key_exists($key, $this->data) || $this->initialized === false)
		) {
			return $this->hydrateElement($key);
		}

		return $element;
	}

	/**
	 * Low-level element remover
	 */
	public function __unset(string $key)
	{
		// first initialize, otherwise a later initialization
		// might bring back the element that was unset
		$this->initialize();

		return parent::__unset($key);
	}

	/**
	 * Creates chunks of the same size.
	 * The last chunk may be smaller
	 *
	 * @param int $size Number of elements per chunk
	 * @return static A new collection with an element for each chunk and
	 *                a sub collection in each chunk
	 */
	public function chunk(int $size): static
	{
		// chunking at least requires the collection structure
		$this->initialize();

		return parent::chunk($size);
	}

	/**
	 * Counts all elements
	 */
	public function count(): int
	{
		$this->initialize();

		return parent::count();
	}

	/**
	 * Returns the current element
	 * @deprecated
	 * @todo Remove in v6
	 *
	 * @return TValue
	 */
	public function current(): mixed
	{
		$current = parent::current();

		// `$current === null` could mean "empty collection"
		// or "element found but not hydrated"
		if ($current === null && $key = $this->key()) {
			return $this->hydrateElement($key);
		}

		return $current;
	}

	/**
	 * Returns the elements in reverse order
	 */
	public function flip(): static
	{
		// flipping at least requires the collection structure
		$this->initialize();

		return parent::flip();
	}

	/**
	 * Filters elements by one of the
	 * predefined filter methods, by a
	 * custom filter function or an array of filters
	 */
	public function filter(string|array|Closure $field, ...$args): static
	{
		// to filter through values, we need all values present
		$this->hydrate();

		return parent::filter($field, ...$args);
	}

	/**
	 * Returns the first element
	 *
	 * @return TValue
	 */
	public function first()
	{
		// returning a specific offset requires the collection structure
		$this->initialize();

		$first = parent::first();

		// `$first === null` could mean "empty collection"
		// or "element found but not hydrated"
		if ($first === null && $key = array_key_first($this->data)) {
			return $this->hydrateElement($key);
		}

		return $first;
	}

	/**
	 * Returns an iterator for the elements
	 * @return \Iterator<TKey, TValue>
	 */
	public function getIterator(): Iterator
	{
		// ensure we are looping over all possible elements
		$this->initialize();

		foreach ($this->data as $key => $value) {
			if ($value === null) {
				$value = $this->hydrateElement($key);
			}

			yield $key => $value;
		}
	}

	/**
	 * Checks by key if an element is included
	 * @param TKey $key
	 */
	public function has(mixed $key): bool
	{
		$this->initialize();

		return parent::has($key);
	}

	/**
	 * Ensures that all collection elements are loaded,
	 * essentially converting the lazy collection into a
	 * normal collection
	 */
	public function hydrate(): void
	{
		// first ensure all keys are initialized
		$this->initialize();

		// skip another hydration loop if no longer needed
		if ($this->hydrated === true) {
			return;
		}

		foreach ($this->data as $key => $value) {
			if ($value === null) {
				$this->hydrateElement($key);
			}
		}

		$this->hydrated = true;
	}

	/**
	 * Loads a collection element, sets it in `$this->data[$key]`
	 * and returns the hydrated object value (or `null` if the
	 * element does not exist in the collection); to be
	 * implemented in each specific collection
	 */
	abstract protected function hydrateElement(string $key): object|null;

	/**
	 * Ensures that the keys for all valid collection elements
	 * are loaded in the `$data` array and sets `$initialized`
	 * to `true` afterwards; to be implemented in each collection
	 * that wants to use lazy initialization; be sure to keep
	 * existing `$data` values and not overwrite the entire array
	 */
	public function initialize(): void
	{
		if ($this->initialized === true) {
			return;
		}

		throw new LogicException(static::class . ' class does not implement `initialize()` method that is required for lazy initialization'); // @codeCoverageIgnore
	}

	/**
	 * Returns an array of all keys
	 */
	public function keys(): array
	{
		// ensure we are returning all possible keys
		$this->initialize();

		return parent::keys();
	}

	/**
	 * Tries to find the key for the given element
	 *
	 * @param TValue $needle the element to search for
	 * @return int|string|false the name of the key or false
	 */
	public function keyOf(mixed $needle): int|string|false
	{
		// quick lookup without having to hydrate the collection
		// (keys in CMS collections are the object IDs)
		if (
			is_object($needle) === true &&
			method_exists($needle, 'id') === true
		) {
			return $needle->id();
		}

		$this->hydrate();
		return parent::keyOf($needle);
	}

	/**
	 * Returns the last element
	 *
	 * @return TValue
	 */
	public function last()
	{
		// returning a specific offset requires the collection structure
		$this->initialize();

		$last = parent::last();

		// `$last === null` could mean "empty collection"
		// or "element found but not hydrated"
		if ($last === null && $key = array_key_last($this->data)) {
			return $this->hydrateElement($key);
		}

		return $last;
	}

	/**
	 * Map a function to each element
	 *
	 * @return $this
	 */
	public function map(callable $callback): static
	{
		// to map a function, we need all values present
		$this->hydrate();

		return parent::map($callback);
	}

	/**
	 * Moves the cursor to the next element
	 * and returns it
	 * @deprecated
	 * @todo Remove in v6
	 *
	 * @return TValue
	 */
	public function next(): mixed
	{
		$this->initialize();

		$next = parent::next();

		// `$next === null` could mean "empty collection"
		// or "element found but not hydrated"
		if ($next === null && $key = $this->key()) {
			return $this->hydrateElement($key);
		}

		return $next;
	}

	/**
	 * Returns the nth element from the collection
	 *
	 * @return TValue|null
	 */
	public function nth(int $n)
	{
		// returning a specific offset requires the collection structure
		$this->initialize();

		$nth = parent::nth($n);

		// `$nth === null` could mean "empty collection"
		// or "element found but not hydrated"
		if ($nth === null) {
			$key = array_keys($this->data)[$n] ?? null;

			if (is_string($key) === true) {
				return $this->hydrateElement($key);
			}
		}

		return $nth;
	}

	/**
	 * Moves the cursor to the previous element
	 * and returns it
	 * @deprecated
	 * @todo Remove in v6
	 *
	 * @return TValue
	 */
	public function prev(): mixed
	{
		$this->initialize();

		$prev = parent::prev();

		// `$prev === null` could mean "empty collection"
		// or "element found but not hydrated"
		if ($prev === null && $key = $this->key()) {
			return $this->hydrateElement($key);
		}

		return $prev;
	}

	/**
	 * Returns a new collection consisting of random elements,
	 * from the original collection, shuffled or ordered
	 */
	public function random(int $count = 1, bool $shuffle = false): static
	{
		// picking random elements at least requires the collection structure
		$this->initialize();

		return parent::random($count, $shuffle);
	}

	/**
	 * Shuffle all elements
	 */
	public function shuffle(): static
	{
		// shuffling at least requires the collection structure
		$this->initialize();

		return parent::shuffle();
	}

	/**
	 * Returns a slice of the object
	 *
	 * @param int $offset The optional index to start the slice from
	 * @param int|null $limit The optional number of elements to return
	 * @return $this|static
	 * @psalm-return ($offset is 0 && $limit is null ? $this : static)
	 */
	public function slice(
		int $offset = 0,
		int|null $limit = null
	): static {
		// returning a specific subset requires the collection structure
		$this->initialize();

		return parent::slice($offset, $limit);
	}

	/**
	 * Sorts the elements by any number of fields
	 *
	 * ```php
	 * $collection->sort('fieldName');
	 * $collection->sort('fieldName', 'desc');
	 * $collection->sort('fieldName', 'asc', SORT_REGULAR);
	 * $collection->sort(fn ($a) => ...);
	 * ```
	 *
	 * @param string|callable $field Field name or value callback to sort by
	 * @param string|null $direction asc or desc
	 * @param int|null $method The sort flag, SORT_REGULAR, SORT_NUMERIC etc.
	 * @return $this|static
	 */
	public function sort(...$args): static
	{
		// to sort through values, we need all values present
		$this->hydrate();

		return parent::sort(...$args);
	}

	/**
	 * Converts all objects in the collection
	 * to an array. This can also take a callback
	 * function to further modify the array result.
	 */
	public function toArray(Closure|null $map = null): array
	{
		// to export an array, we need all values present
		$this->hydrate();

		return parent::toArray($map);
	}

	/**
	 * Returns a non-associative array
	 * with all values. If a mapping Closure is passed,
	 * all values are processed by the Closure.
	 */
	public function values(Closure|null $map = null): array
	{
		// to export an array, we need all values present
		$this->hydrate();

		return parent::values($map);
	}
}
