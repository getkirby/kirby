<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Collection as BaseCollection;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

/**
 * The Collection class serves as foundation
 * for the Pages, Files, Users and Structure
 * classes. It handles object validation and sets
 * the parent collection property for each object.
 * The `getAttribute` method is also adjusted to
 * handle values from Field objects correctly, so
 * those can be used in filters as well.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @template TValue
 * @extends \Kirby\Toolkit\Collection<TValue>
 */
class Collection extends BaseCollection
{
	use HasMethods;

	/**
	 * @var \Kirby\Cms\Pagination|null
	 */
	protected $pagination;

	/**
	 * Creates a new Collection with the given objects
	 *
	 * @param object|null $parent Stores the parent object,
	 *                            which is needed in some collections
	 *                            to get the finder methods right
	 */
	public function __construct(
		iterable $objects = [],
		protected object|null $parent = null
	) {
		foreach ($objects as $object) {
			$this->add($object);
		}
	}

	public function __call(string $key, $arguments)
	{
		// collection methods
		if ($this->hasMethod($key) === true) {
			return $this->callMethod($key, $arguments);
		}
	}

	/**
	 * Internal setter for each object in the Collection;
	 * override from the Toolkit Collection is needed to
	 * make the CMS collections case-sensitive;
	 * child classes can override it again to add validation
	 * and custom behavior depending on the object type
	 *
	 * @param TValue $object
	 */
	public function __set(string $id, $object): void
	{
		$this->data[$id] = $object;
	}

	/**
	 * Internal remover for each object in the Collection;
	 * override from the Toolkit Collection is needed to
	 * make the CMS collections case-sensitive
	 */
	public function __unset(string $id)
	{
		unset($this->data[$id]);
	}

	/**
	 * Adds a single object or
	 * an entire second collection to the
	 * current collection
	 *
	 * @param static|TValue|array $object
	 * @return $this
	 */
	public function add($object): static
	{
		if ($object instanceof self) {
			$this->data = [...$this->data, ...$object->data];
		} elseif (
			is_object($object) === true &&
			method_exists($object, 'id') === true
		) {
			$this->__set($object->id(), $object);
		} else {
			$this->append($object);
		}

		return $this;
	}

	/**
	 * Appends an element to the data array
	 *
	 * ```php
	 * $collection->append($object);
	 * $collection->append('key', $object);
	 * ```
	 *
	 * @param string|TValue ...$args
	 * @return $this
	 */
	public function append(...$args): static
	{
		if (count($args) === 1) {
			// try to determine the key from the provided item
			if (
				is_object($args[0]) === true &&
				is_callable([$args[0], 'id']) === true
			) {
				return parent::append($args[0]->id(), $args[0]);
			}

			return parent::append($args[0]);
		}

		return parent::append(...$args);
	}

	/**
	 * Find a single element by an attribute and its value
	 *
	 * @return TValue|null
	 */
	public function findBy(string $attribute, $value)
	{
		// $value: cast UUID object to string to allow uses
		// like `$pages->findBy('related', $page->uuid())`
		if ($value instanceof Uuid) {
			$value = $value->toString();
		}

		return parent::findBy($attribute, $value);
	}

	/**
	 * Groups the items by a given field or callback. Returns a collection
	 * with an item for each group and a collection for each group.
	 *
	 * @param string|\Closure $field
	 * @param bool $caseInsensitive Ignore upper/lowercase for group names
	 * @throws \Kirby\Exception\Exception
	 */
	public function group(
		$field,
		bool $caseInsensitive = true
	): self {
		if (is_string($field) === true) {
			$groups = new self([], $this->parent());

			foreach ($this->data as $key => $item) {
				$value = $this->getAttribute($item, $field);

				// make sure that there's always a proper value to group by
				if (!$value) {
					throw new InvalidArgumentException(
						message: 'Invalid grouping value for key: ' . $key
					);
				}

				$value = (string)$value;

				// ignore upper/lowercase for group names
				if ($caseInsensitive) {
					$value = Str::lower($value);
				}

				if (isset($groups->data[$value]) === false) {
					// create a new entry for the group if it does not exist yet
					$groups->data[$value] = new static([$key => $item]);
				} else {
					// add the item to an existing group
					$groups->data[$value]->set($key, $item);
				}
			}

			return $groups;
		}

		// use the parent method but unwrap the Toolkit collection
		// and rewrap it as a Cms collection
		return new self(
			parent::group($field, $caseInsensitive)->data,
			$this->parent()
		);
	}

	/**
	 * Checks if the given object or id
	 * is in the collection
	 *
	 * @param string|TValue $key
	 */
	public function has($key): bool
	{
		if (is_object($key) === true) {
			$key = $key->id();
		}

		return parent::has($key);
	}

	/**
	 * Correct position detection for objects.
	 * The method will automatically detect objects
	 * or ids and then search accordingly.
	 *
	 * @param string|TValue $needle
	 */
	public function indexOf($needle): int|false
	{
		if (is_string($needle) === true) {
			return array_search($needle, $this->keys());
		}

		return array_search($needle->id(), $this->keys());
	}

	/**
	 * Returns a Collection without the given element(s)
	 *
	 * @param string|array|object ...$keys any number of keys,
	 *                                     passed as individual arguments
	 */
	public function not(string|array|object ...$keys): static
	{
		$collection = $this->clone();

		foreach ($keys as $key) {
			if (is_array($key) === true) {
				return $this->not(...$key);
			}

			if ($key instanceof BaseCollection) {
				$collection = $collection->not(...$key->keys());
			} elseif (is_object($key) === true) {
				$key = $key->id();
			}

			unset($collection->{$key});
		}

		return $collection;
	}

	/**
	 * Add pagination and return a sliced set of data.
	 *
	 * @return $this|static
	 */
	public function paginate(...$arguments): static
	{
		$this->pagination = Pagination::for($this, ...$arguments);

		// slice and clone the collection according to the pagination
		return $this->slice(
			$this->pagination->offset(),
			$this->pagination->limit()
		);
	}

	/**
	 * Get the previously added pagination object
	 */
	public function pagination(): Pagination|null
	{
		return $this->pagination;
	}

	/**
	 * Returns the parent model
	 */
	public function parent(): object|null
	{
		return $this->parent;
	}

	/**
	 * Prepends an element to the data array
	 *
	 * ```php
	 * $collection->prepend($object);
	 * $collection->prepend('key', $object);
	 * ```
	 *
	 * @param string|TValue ...$args
	 * @return $this
	 */
	public function prepend(...$args): static
	{
		if (count($args) === 1) {
			// try to determine the key from the provided item
			if (
				is_object($args[0]) === true &&
				is_callable([$args[0], 'id']) === true
			) {
				return parent::prepend($args[0]->id(), $args[0]);
			}

			return parent::prepend($args[0]);
		}

		return parent::prepend(...$args);
	}

	/**
	 * Runs a combination of filter, sort, not,
	 * offset, limit, search and paginate on the collection.
	 * Any part of the query is optional.
	 */
	public function query(array $arguments = []): static
	{
		$paginate = $arguments['paginate'] ?? null;
		$search   = $arguments['search'] ?? null;

		unset($arguments['paginate']);

		$result = parent::query($arguments);

		if (empty($search) === false) {
			$result = match (true) {
				is_array($search) => $result->search(
					$search['query'] ?? null,
					$search['options'] ?? []
				),
				default => $result->search($search)
			};
		}

		if (empty($paginate) === false) {
			$result = $result->paginate($paginate);
		}

		return $result;
	}

	/**
	 * Removes an object
	 *
	 * @param string|TValue $key the name of the key
	 */
	public function remove(string|object $key): static
	{
		if (is_object($key) === true) {
			$key = $key->id();
		}

		return parent::remove($key);
	}

	/**
	 * Searches the collection
	 */
	public function search(
		string|null $query = null,
		string|array $params = []
	): static {
		return Search::collection($this, $query, $params);
	}

	/**
	 * Converts all objects in the collection
	 * to an array. This can also take a callback
	 * function to further modify the array result.
	 */
	public function toArray(Closure|null $map = null): array
	{
		return parent::toArray(
			$map ?? fn ($object) => $object->toArray()
		);
	}

	/**
	 * Updates an object in the collection
	 *
	 * @return $this
	 */
	public function update(string|object $key, $object = null): static
	{
		if (is_object($key) === true) {
			return $this->update($key->id(), $key);
		}

		return $this->set($key, $object);
	}
}
