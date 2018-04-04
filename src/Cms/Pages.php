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
     * Cache for the index
     *
     * @var null|Pages
     */
    protected $index = null;

    /**
     * Returns all children for each page in the array
     *
     * @return Pages
     */
    public function children(): Pages
    {
        $children = new Pages([], $this->parent);

        foreach ($this->data as $pageKey => $page) {
            foreach ($page->children() as $childKey => $child) {
                $children->data[$childKey] = $child;
            }
        }

        return $children;
    }

    /**
     * Creates a pages collection from an array of props
     *
     * @param array $pages
     * @param Model $parent
     * @param array $inject
     * @param string $class
     * @return Pages
     */
    public static function factory(array $pages, Model $parent = null, array $inject = [], string $class = Page::class)
    {
        $children = new static([], $parent);

        foreach ($pages as $props) {
            $page = $class::factory($props + $inject + [
                'collection' => $children
            ]);

            $children->data[$page->id()] = $page;
        }

        return $children;
    }

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
     * Create a recursive flat index of all
     * pages and subpages, etc.
     *
     * @return Pages
     */
    public function index(): Pages
    {
        if (is_a($this->index, Children::class) === true) {
            return $this->index;
        }

        $this->index = new Children([], $this->parent);

        foreach($this->data as $pageKey => $page) {
            $this->index->data[$pageKey] = $page;

            foreach ($page->index() as $childKey => $child) {
                $this->index->data[$childKey] = $child;
            }
        }

        return $this->index;
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
     * Include all given items in the collection
     *
     * @return self
     */
    public function merge(...$args): self
    {
        // merge multiple arguments at once
        if (count($args) > 1) {
            $collection = clone $this;
            foreach ($args as $arg) {
                $collection = $collection->merge($arg);
            }
            return $collection;
        }

        // merge all parent drafts
        if ($args[0] === 'drafts') {
            if ($parent = $this->parent()) {
                return $this->merge($parent->drafts());
            }

            return $this;
        }

        // merge an entire collection
        if (is_a($args[0], static::class) === true) {
            $collection = clone $this;
            $collection->data = array_merge($collection->data, $args[0]->data);
            return $collection;
        }

        // append a single page
        if (is_a($args[0], Page::class) === true) {
            $collection = clone $this;
            return $collection->set($args[0]->id(), $args[0]);
        }

        // merge an array
        if (is_array($args[0]) === true) {
            $collection = clone $this;
            foreach ($args[0] as $arg) {
                $collection = $collection->merge($arg);
            }
            return $collection;
        }

        if (is_string($args[0]) === true) {
            return $this->merge(App::instance()->site()->find($args[0]));
        }

        return $this;
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
