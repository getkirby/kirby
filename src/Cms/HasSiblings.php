<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Collection;

/**
 * This trait is used by pages, files and users
 * to handle navigation through parent collections
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @template TCollection of \Kirby\Toolkit\Collection
 */
trait HasSiblings
{
	/**
	 * Checks if there's a next item in the collection
	 *
	 * @param TCollection|null $collection
	 */
	public function hasNext(Collection|null $collection = null): bool
	{
		return $this->next($collection) !== null;
	}

	/**
	 * Checks if there's a previous item in the collection
	 *
	 * @param TCollection|null $collection
	 */
	public function hasPrev(Collection|null $collection = null): bool
	{
		return $this->prev($collection) !== null;
	}

	/**
	 * Returns the position / index in the collection
	 *
	 * @param TCollection|null $collection
	 */
	public function indexOf(Collection|null $collection = null): int|false
	{
		$collection ??= $this->siblingsCollection();
		return $collection->indexOf($this);
	}

	/**
	 * Checks if the item is the first in the collection
	 *
	 * @param TCollection|null $collection
	 */
	public function isFirst(Collection|null $collection = null): bool
	{
		$collection ??= $this->siblingsCollection();
		return $collection->first()->is($this);
	}

	/**
	 * Checks if the item is the last in the collection
	 *
	 * @param TCollection|null $collection
	 */
	public function isLast(Collection|null $collection = null): bool
	{
		$collection ??= $this->siblingsCollection();
		return $collection->last()->is($this);
	}

	/**
	 * Checks if the item is at a certain position
	 *
	 * @param TCollection|null $collection
	 */
	public function isNth(int $n, Collection|null $collection = null): bool
	{
		return $this->indexOf($collection) === $n;
	}

	/**
	 * Returns the next item in the collection if available
	 * @todo `static` return type hint is not 100% accurate because of
	 *       quirks in the `Form` classes; would break if enforced
	 *       (https://github.com/getkirby/kirby/pull/5175)
	 *
	 * @param TCollection|null $collection
	 * @return static|null
	 */
	public function next($collection = null)
	{
		$collection ??= $this->siblingsCollection();
		return $collection->nth($this->indexOf($collection) + 1);
	}

	/**
	 * Returns the end of the collection starting after the current item
	 *
	 * @param TCollection|null $collection
	 * @return TCollection
	 */
	public function nextAll(Collection|null $collection = null): Collection
	{
		$collection ??= $this->siblingsCollection();
		return $collection->slice($this->indexOf($collection) + 1);
	}

	/**
	 * Returns the previous item in the collection if available
	 * @todo `static` return type hint is not 100% accurate because of
	 *       quirks in the `Form` classes; would break if enforced
	 *       (https://github.com/getkirby/kirby/pull/5175)
	 *
	 * @param TCollection|null $collection
	 * @return static|null
	 */
	public function prev(Collection|null $collection = null)
	{
		$collection ??= $this->siblingsCollection();
		return $collection->nth($this->indexOf($collection) - 1);
	}

	/**
	 * Returns the beginning of the collection before the current item
	 *
	 * @param TCollection|null $collection
	 * @return TCollection
	 */
	public function prevAll(Collection|null $collection = null): Collection
	{
		$collection ??= $this->siblingsCollection();
		return $collection->slice(0, $this->indexOf($collection));
	}

	/**
	 * Returns all sibling elements
	 *
	 * @return TCollection
	 */
	public function siblings(bool $self = true): Collection
	{
		$siblings = $this->siblingsCollection();

		if ($self === false) {
			return $siblings->not($this);
		}

		return $siblings;
	}

	/**
	 * Returns the collection of siblings
	 * @return TCollection
	 */
	abstract protected function siblingsCollection(): Collection;
}
