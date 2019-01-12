<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Str;

trait HasChildren
{

    /**
     * The Pages collection
     *
     * @var Pages
     */
    public $children;

    /**
     * The list of available drafts
     *
     * @var Pages
     */
    public $drafts;

    /**
     * Returns the Pages collection
     *
     * @return Pages
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
     * @return Pages
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
     * @return Page|null
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
     * @return Pages
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
     * @return Pages
     */
    public function find(...$arguments)
    {
        return $this->children()->find(...$arguments);
    }

    /**
     * Finds a single page or draft
     *
     * @return Page|null
     */
    public function findPageOrDraft(string $path)
    {
        return $this->children()->find($path) ?? $this->drafts()->find($path);
    }

    /**
     * Returns a collection of all children of children
     *
     * @return Pages
     */
    public function grandChildren(): Pages
    {
        return $this->children()->children();
    }

    /**
     * Checks if the model has any children
     *
     * @return boolean
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Checks if the model has any drafts
     *
     * @return boolean
     */
    public function hasDrafts(): bool
    {
        return $this->drafts()->count() > 0;
    }

    /**
     * Deprecated! Use Page::hasUnlistedChildren
     *
     * @return boolean
     */
    public function hasInvisibleChildren(): bool
    {
        return $this->children()->invisible()->count() > 0;
    }

    /**
     * Checks if the page has any listed children
     *
     * @return boolean
     */
    public function hasListedChildren(): bool
    {
        return $this->children()->listed()->count() > 0;
    }

    /**
     * Checks if the page has any unlisted children
     *
     * @return boolean
     */
    public function hasUnlistedChildren(): bool
    {
        return $this->children()->unlisted()->count() > 0;
    }

    /**
     * Deprecated! Use Page::hasListedChildren
     *
     * @return boolean
     */
    public function hasVisibleChildren(): bool
    {
        return $this->children()->listed()->count() > 0;
    }

    /**
     * Creates a flat child index
     *
     * @param bool $drafts
     * @return Pages
     */
    public function index(bool $drafts = false): Pages
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
