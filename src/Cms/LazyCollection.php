<?php

namespace Kirby\Cms;

use Closure;
use Iterator;

/**
 * The LazyCollection class is a variant of the CMS
 * Collection that is only initialized with keys for
 * each collection element. Collection values (= objects)
 * are loaded and initialized lazily when they are
 * first used.
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
	 * Temporary auto-hydration whenever a collection
	 * method is called; some methods may not need raw
	 * access to all collection data, so performance
	 * will be improved if methods call hydration
	 * themselves only if they need it
	 * @deprecated
	 * @todo Remove this in v6
	 */
	public function __call(string $key, $arguments)
	{
		$this->hydrateAll();
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
		if ($element === null && array_key_exists($key, $this->data)) {
			return $this->hydrateElement($key);
		}

		return $element;
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
	 * Filters elements by one of the
	 * predefined filter methods, by a
	 * custom filter function or an array of filters
	 */
	public function filter(string|array|Closure $field, ...$args): static
	{
		// to filter through values, we need all values present
		$this->hydrateAll();

		return parent::filter($field, ...$args);
	}

	/**
	 * Returns the first element
	 *
	 * @return TValue
	 */
	public function first()
	{
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
		foreach ($this->data as $key => $value) {
			if ($value === null) {
				$value = $this->hydrateElement($key);
			}

			yield $key => $value;
		}
	}

	/**
	 * Ensures that all collection elements are loaded,
	 * essentially converting the lazy collection into a
	 * normal collection
	 */
	public function hydrateAll(): void
	{
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
	 * and returns the hydrated object value; to be implemented
	 * in each specific collection
	 */
	abstract protected function hydrateElement(string $key): object;

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

		$this->hydrateAll();
		return parent::keyOf($needle);
	}

	/**
	 * Returns the last element
	 *
	 * @return TValue
	 */
	public function last()
	{
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
		$this->hydrateAll();

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
		$prev = parent::prev();

		// `$prev === null` could mean "empty collection"
		// or "element found but not hydrated"
		if ($prev === null && $key = $this->key()) {
			return $this->hydrateElement($key);
		}

		return $prev;
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
		$this->hydrateAll();

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
		$this->hydrateAll();

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
		$this->hydrateAll();

		return parent::values($map);
	}
}
