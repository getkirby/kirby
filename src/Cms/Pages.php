<?php

namespace Kirby\Cms;

use Exception;

/**
 * The Pages collection contains
 * any number and mixture of page objects
 * They don't necessarily have to belong
 * to the same parent in comparison to
 * the Children collection. The Children
 * collection is based on the Pages
 * collection though.
 *
 * Pages collection can be constructed very
 * easily:
 *
 * ```php
 * $collection = new Pages([
 *   new Page(['id' => 'project-a']),
 *   new Page(['id' => 'project-b']),
 *   new Page(['id' => 'project-c']),
 * ]);
 * ```
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Pages extends Collection
{

    /**
     * Only accepts Page objects
     *
     * @var string
     */
    protected static $accept = Page::class;

    /**
     * Initialize the PagesFinder class,
     * which is handling findBy and find
     * methods
     *
     * @return PagesFinder
     */
    protected function finder()
    {
        return new PagesFinder($this);
    }

    /**
     * Custom getter that is able to find
     * extension pages
     *
     * @param string $key
     * @return Page|null
     */
    public function get($key, $default = null)
    {
        if ($item = parent::get($key)) {
            return $item;
        }

        return App::instance()->extension('pages', $key);
    }

    /**
     * Deprecated alias for Pages::unlisted()
     *
     * @return self
     */
    public function invisible(): self
    {
        return $this->filterBy('isUnlisted', '==', true);
    }

    /**
     * Returns all listed pages in the collection
     *
     * @return self
     */
    public function listed(): self
    {
        return $this->filterBy('isListed', '==', true);
    }

    /**
     * Returns all unlisted pages in the collection
     *
     * @return self
     */
    public function unlisted(): self
    {
        return $this->filterBy('isUnlisted', '==', true);
    }

    /**
     * Deprecated alias for Pages::listed()
     *
     * @return self
     */
    public function visible(): self
    {
        return $this->filterBy('isListed', '==', true);
    }

}
