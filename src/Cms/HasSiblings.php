<?php

namespace Kirby\Cms;

/**
 * This trait is used by pages, files and users
 * to handle navigation through parent collections
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
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
        return $this->siblingsCollection()->indexOf($this);
    }

    /**
     * Returns the next item in the collection if available
     *
     * @return \Kirby\Cms\Model|null
     */
    public function next()
    {
        return $this->siblingsCollection()->nth($this->indexOf() + 1);
    }

    /**
     * Returns the end of the collection starting after the current item
     *
     * @return \Kirby\Cms\Collection
     */
    public function nextAll()
    {
        return $this->siblingsCollection()->slice($this->indexOf() + 1);
    }

    /**
     * Returns the previous item in the collection if available
     *
     * @return \Kirby\Cms\Model|null
     */
    public function prev()
    {
        return $this->siblingsCollection()->nth($this->indexOf() - 1);
    }

    /**
     * Returns the beginning of the collection before the current item
     *
     * @return \Kirby\Cms\Collection
     */
    public function prevAll()
    {
        return $this->siblingsCollection()->slice(0, $this->indexOf());
    }

    /**
     * Returns all sibling elements
     *
     * @param bool $self
     * @return \Kirby\Cms\Collection
     */
    public function siblings(bool $self = true)
    {
        $siblings = $this->siblingsCollection();

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
        return $this->siblingsCollection()->first()->is($this);
    }

    /**
     * Checks if the item is the last in the collection
     *
     * @return bool
     */
    public function isLast(): bool
    {
        return $this->siblingsCollection()->last()->is($this);
    }

    /**
     * Checks if the item is at a certain position
     *
     * @param int $n
     * @return bool
     */
    public function isNth(int $n): bool
    {
        return $this->indexOf() === $n;
    }
}
