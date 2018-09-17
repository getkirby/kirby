<?php

namespace Kirby\Cms;

/**
 * This trait is used by pages, files and users
 * to handle navigation through parent collections
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
trait HasSiblings
{

    /**
     * Returns the position / index in the collection
     *
     * @return int
     */
    public function indexOf(): int
    {
        return $this->collection()->indexOf($this);
    }

    /**
     * Returns the next item in the collection if available
     *
     * @return Model|null
     */
    public function next()
    {
        return $this->collection()->nth($this->indexOf() + 1);
    }

    /**
     * Returns the end of the collection starting after the current item
     *
     * @return Collection
     */
    public function nextAll()
    {
        return $this->collection()->slice($this->indexOf() + 1);
    }

    /**
     * Returns the previous item in the collection if available
     *
     * @return Model|null
     */
    public function prev()
    {
        return $this->collection()->nth($this->indexOf() - 1);
    }

    /**
     * Returns the beginning of the collection before the current item
     *
     * @return Collection
     */
    public function prevAll()
    {
        return $this->collection()->slice(0, $this->indexOf());
    }

    /**
     * Returns all sibling elements
     *
     * @param bool $self
     * @return Collection
     */
    public function siblings(bool $self = true)
    {
        $siblings = $this->collection();

        if ($self === false) {
            return $siblings->not($this);
        }

        return $siblings;
    }

    /**
     * Checks if there's a next item in the collection
     *
     * @return bool
     */
    public function hasNext(): bool
    {
        return $this->next() !== null;
    }

    /**
     * Checks if there's a previous item in the collection
     *
     * @return bool
     */
    public function hasPrev(): bool
    {
        return $this->prev() !== null;
    }

    /**
     * Checks if the item is the first in the collection
     *
     * @return bool
     */
    public function isFirst(): bool
    {
        return $this->collection()->first()->is($this);
    }

    /**
     * Checks if the item is the last in the collection
     *
     * @return bool
     */
    public function isLast(): bool
    {
        return $this->collection()->last()->is($this);
    }

    /**
     * Checks if the item is at a certain position
     *
     * @return bool
     */
    public function isNth(int $n): bool
    {
        return $this->indexOf() === $n;
    }
}
