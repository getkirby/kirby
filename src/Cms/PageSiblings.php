<?php

namespace Kirby\Cms;

/**
 * PageSiblings
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
trait PageSiblings
{

    /**
     * @deprecated 3.0.0 Use `Page::hasNextUnlisted` instead
     * @return boolean
     */
    public function hasNextInvisible(): bool
    {
        return $this->hasNextUnlisted();
    }

    /**
     * Checks if there's a next listed
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasNextListed(): bool
    {
        return $this->nextListed() !== null;
    }

    /**
     * Checks if there's a next unlisted
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasNextUnlisted(): bool
    {
        return $this->nextUnlisted() !== null;
    }

    /**
     * @deprecated 3.0.0 Use `Page::hasNextListed` instead
     * @return boolean
     */
    public function hasNextVisible(): bool
    {
        return $this->hasNextListed();
    }

    /**
     * @deprecated 3.0.0 Use `Page::hasPrevUnlisted` instead
     * @return boolean
     */
    public function hasPrevInvisible(): bool
    {
        return $this->hasPrevUnlisted();
    }

    /**
     * Checks if there's a previous listed
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasPrevListed(): bool
    {
        return $this->prevListed() !== null;
    }

    /**
     * Checks if there's a previous unlisted
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasPrevUnlisted(): bool
    {
        return $this->prevUnlisted() !== null;
    }

    /**
     * @deprecated 3.0.0 Use `Page::hasPrevListed instead`
     * @return boolean
     */
    public function hasPrevVisible(): bool
    {
        return $this->hasPrevListed();
    }

    /**
     * @deprecated 3.0.0 Use `Page::nextUnlisted()` instead
     * @return self|null
     */
    public function nextInvisible()
    {
        return $this->nextUnlisted();
    }

    /**
     * Returns the next listed page if it exists
     *
     * @return Kirby\Cms\Page|null
     */
    public function nextListed()
    {
        return $this->nextAll()->listed()->first();
    }

    /**
     * Returns the next unlisted page if it exists
     *
     * @return Kirby\Cms\Page|null
     */
    public function nextUnlisted()
    {
        return $this->nextAll()->unlisted()->first();
    }

    /**
     * @deprecated 3.0.0 Use `Page::prevListed()` instead
     * @return self|null
     */
    public function nextVisible()
    {
        return $this->nextListed();
    }

    /**
     * @deprecated 3.0.0 Use `Page::prevUnlisted()` instead
     * @return self|null
     */
    public function prevInvisible()
    {
        return $this->prevUnlisted();
    }

    /**
     * Returns the previous listed page
     *
     * @return Kirby\Cms\Page|null
     */
    public function prevListed()
    {
        return $this->prevAll()->listed()->last();
    }

    /**
     * Returns the previous unlisted page
     *
     * @return Kirby\Cms\Page|null
     */
    public function prevUnlisted()
    {
        return $this->prevAll()->unlisted()->first();
    }

    /**
     * @deprecated 3.0.0 Use `Page::prevListed()` instead
     * @return self|null
     */
    public function prevVisible()
    {
        return $this->prevListed();
    }

    /**
     * Private siblings collector
     *
     * @return Kirby\Cms\Collection
     */
    protected function siblingsCollection()
    {
        if ($this->isDraft() === true) {
            return $this->parentModel()->drafts();
        } else {
            return $this->parentModel()->children();
        }
    }

    /**
     * Returns siblings with the same template
     *
     * @param bool $self
     * @return Kirby\Cms\Pages
     */
    public function templateSiblings(bool $self = true)
    {
        return $this->siblings($self)->filterBy('intendedTemplate', $this->intendedTemplate()->name());
    }
}
