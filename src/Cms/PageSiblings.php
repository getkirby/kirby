<?php

namespace Kirby\Cms;

/**
 * PageSiblings
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait PageSiblings
{
    /**
     * Checks if there's a next listed
     * page in the siblings collection
     *
     * @param \Kirby\Cms\Collection|null $collection
     *
     * @return bool
     */
    public function hasNextListed($collection = null): bool
    {
        return $this->nextListed($collection) !== null;
    }

    /**
     * Checks if there's a next unlisted
     * page in the siblings collection
     *
     * @param \Kirby\Cms\Collection|null $collection
     *
     * @return bool
     */
    public function hasNextUnlisted($collection = null): bool
    {
        return $this->nextUnlisted($collection) !== null;
    }

    /**
     * Checks if there's a previous listed
     * page in the siblings collection
     *
     * @param \Kirby\Cms\Collection|null $collection
     *
     * @return bool
     */
    public function hasPrevListed($collection = null): bool
    {
        return $this->prevListed($collection) !== null;
    }

    /**
     * Checks if there's a previous unlisted
     * page in the siblings collection
     *
     * @param \Kirby\Cms\Collection|null $collection
     *
     * @return bool
     */
    public function hasPrevUnlisted($collection = null): bool
    {
        return $this->prevUnlisted($collection) !== null;
    }

    /**
     * Returns the next listed page if it exists
     *
     * @param \Kirby\Cms\Collection|null $collection
     *
     * @return \Kirby\Cms\Page|null
     */
    public function nextListed($collection = null)
    {
        return $this->nextAll($collection)->listed()->first();
    }

    /**
     * Returns the next unlisted page if it exists
     *
     * @param \Kirby\Cms\Collection|null $collection
     *
     * @return \Kirby\Cms\Page|null
     */
    public function nextUnlisted($collection = null)
    {
        return $this->nextAll($collection)->unlisted()->first();
    }

    /**
     * Returns the previous listed page
     *
     * @param \Kirby\Cms\Collection|null $collection
     *
     * @return \Kirby\Cms\Page|null
     */
    public function prevListed($collection = null)
    {
        return $this->prevAll($collection)->listed()->last();
    }

    /**
     * Returns the previous unlisted page
     *
     * @param \Kirby\Cms\Collection|null $collection
     *
     * @return \Kirby\Cms\Page|null
     */
    public function prevUnlisted($collection = null)
    {
        return $this->prevAll($collection)->unlisted()->last();
    }

    /**
     * Private siblings collector
     *
     * @return \Kirby\Cms\Collection
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
     * @return \Kirby\Cms\Pages
     */
    public function templateSiblings(bool $self = true)
    {
        return $this->siblings($self)->filter('intendedTemplate', $this->intendedTemplate()->name());
    }
}
