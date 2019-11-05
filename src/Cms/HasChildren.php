<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Str;

/**
 * HasChildren
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
trait HasChildren
{
    /**
     * The Pages collection
     *
     * @var \Kirby\Cms\Pages
     */
    public $children;

    /**
     * The list of available drafts
     *
     * @var \Kirby\Cms\Pages
     */
    public $drafts;

    /**
     * Returns the Pages collection
     *
     * @return \Kirby\Cms\Pages
     */
    public function children()
    {
        if (is_a($this->children, 'Kirby\Cms\Pages') === true) {
            return $this->children;
        }

        return $this->children = Pages::factory($this->inventory()['children'], $this);
    }

    /**
     * Returns all children and drafts at the same time
     *
     * @return \Kirby\Cms\Pages
     */
    public function childrenAndDrafts()
    {
        return $this->children()->merge($this->drafts());
    }

    /**
     * Return a list of ids for the model's
     * toArray method
     *
     * @return array
     */
    protected function convertChildrenToArray(): array
    {
        return $this->children()->keys();
    }

    /**
     * Searches for a child draft by id
     *
     * @param string $path
     * @return \Kirby\Cms\Page|null
     */
    public function draft(string $path)
    {
        $path = str_replace('_drafts/', '', $path);

        if (Str::contains($path, '/') === false) {
            return $this->drafts()->find($path);
        }

        $parts  = explode('/', $path);
        $parent = $this;

        foreach ($parts as $slug) {
            if ($page = $parent->find($slug)) {
                $parent = $page;
                continue;
            }

            if ($draft = $parent->drafts()->find($slug)) {
                $parent = $draft;
                continue;
            }

            return null;
        }

        return $parent;
    }

    /**
     * Return all drafts of the model
     *
     * @return \Kirby\Cms\Pages
     */
    public function drafts()
    {
        if (is_a($this->drafts, 'Kirby\Cms\Pages') === true) {
            return $this->drafts;
        }

        $kirby = $this->kirby();

        // create the inventory for all drafts
        $inventory = Dir::inventory(
            $this->root() . '/_drafts',
            $kirby->contentExtension(),
            $kirby->contentIgnore(),
            $kirby->multilang()
        );

        return $this->drafts = Pages::factory($inventory['children'], $this, true);
    }

    /**
     * Finds one or multiple children by id
     *
     * @param string ...$arguments
     * @return \Kirby\Cms\Page|\Kirby\Cms\Pages
     */
    public function find(...$arguments)
    {
        return $this->children()->find(...$arguments);
    }

    /**
     * Finds a single page or draft
     *
     * @param string $path
     * @return \Kirby\Cms\Page|null
     */
    public function findPageOrDraft(string $path)
    {
        return $this->children()->find($path) ?? $this->drafts()->find($path);
    }

    /**
     * Returns a collection of all children of children
     *
     * @return \Kirby\Cms\Pages
     */
    public function grandChildren()
    {
        return $this->children()->children();
    }

    /**
     * Checks if the model has any children
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Checks if the model has any drafts
     *
     * @return bool
     */
    public function hasDrafts(): bool
    {
        return $this->drafts()->count() > 0;
    }

    /**
     * @deprecated 3.0.0 Use `Page::hasUnlistedChildren()` instead
     * @return bool
     */
    public function hasInvisibleChildren(): bool
    {
        deprecated('$page->hasInvisibleChildren() is deprecated, use $page->hasUnlistedChildren() instead. $page->hasInvisibleChildren() will be removed in Kirby 3.5.0.');

        return $this->hasUnlistedChildren();
    }

    /**
     * Checks if the page has any listed children
     *
     * @return bool
     */
    public function hasListedChildren(): bool
    {
        return $this->children()->listed()->count() > 0;
    }

    /**
     * Checks if the page has any unlisted children
     *
     * @return bool
     */
    public function hasUnlistedChildren(): bool
    {
        return $this->children()->unlisted()->count() > 0;
    }

    /**
     * @deprecated 3.0.0 Use `Page::hasListedChildren()` instead
     * @return bool
     */
    public function hasVisibleChildren(): bool
    {
        deprecated('$page->hasVisibleChildren() is deprecated, use $page->hasListedChildren() instead. $page->hasVisibleChildren() will be removed in Kirby 3.5.0.');

        return $this->hasListedChildren();
    }

    /**
     * Creates a flat child index
     *
     * @param bool $drafts
     * @return \Kirby\Cms\Pages
     */
    public function index(bool $drafts = false)
    {
        if ($drafts === true) {
            return $this->childrenAndDrafts()->index($drafts);
        } else {
            return $this->children()->index();
        }
    }

    /**
     * Sets the Children collection
     *
     * @param array|null $children
     * @return self
     */
    protected function setChildren(array $children = null)
    {
        if ($children !== null) {
            $this->children = Pages::factory($children, $this);
        }

        return $this;
    }

    /**
     * Sets the Drafts collection
     *
     * @param array|null $drafts
     * @return self
     */
    protected function setDrafts(array $drafts = null)
    {
        if ($drafts !== null) {
            $this->drafts = Pages::factory($drafts, $this, true);
        }

        return $this;
    }
}
