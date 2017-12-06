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
trait HasSiblings {

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
     * Returns the position / index in the collection
     *
     * @return int
     */
    public function indexOf(): int
    {
        return $this->collection()->indexOf($this);
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
     * Returns the next item in the collection if available
     *
     * @return Object|null
     */
    public function next()
    {
        if ($collection = $this->collection()) {
            return $collection->nth($this->indexOf() + 1);
        }

        return null;
    }

    /**
     * Returns the end of the collection starting after the current item
     *
     * @return Collection
     */
    public function nextAll()
    {
        if ($collection = $this->collection()) {
            return $collection->slice($this->indexOf() + 1);
        }

        return null;
    }

    /**
     * Returns the previous item in the collection if available
     *
     * @return Object|null
     */
    public function prev()
    {
        if ($collection = $this->collection()) {
            return $collection->nth($this->indexOf() - 1);
        }

        return null;
    }

    /**
     * Returns the beginning of the collection before the current item
     *
     * @return Collection
     */
    public function prevAll()
    {
        if ($collection = $this->collection()) {
            return $collection->slice(0, $this->indexOf());
        }

        return null;
    }

    /**
     * Returns all sibling elements
     *
     * @return Collection
     */
    public function siblings()
    {
        return $this->collection();
    }

}
