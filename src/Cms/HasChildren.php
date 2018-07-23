<?php

namespace Kirby\Cms;

trait HasChildren
{

    /**
     * The Pages collection
     *
     * @var Pages
     */
    protected $children;

    /**
     * The list of available drafts
     *
     * @var Pages
     */
    protected $drafts;

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
     * @return PageDraft|null
     */
    public function draft(string $path)
    {
        return PageDraft::seek($this, $path);
    }

    /**
     * Return all drafts for the site
     *
     * @return Pages
     */
    public function drafts(): Pages
    {
        if (is_a($this->drafts, 'Kirby\Cms\Pages') === true) {
            return $this->drafts;
        }

        $inventory = Dir::inventory($this->root() . '/_drafts');

        return $this->drafts = Pages::factory($inventory['children'], $this, 'Kirby\Cms\PageDraft');
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
     * @return Pages
     */
    public function index(): Pages
    {
        return $this->children()->index();
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
            $this->drafts = Pages::factory($drafts, $this, 'Kirby\Cms\PageDraft');
        }

        return $this;
    }
}
