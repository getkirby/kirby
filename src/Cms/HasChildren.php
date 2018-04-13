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
     * Returns the Children collection
     *
     * Overwrite this for specific Children
     * fetching logic for each model.
     *
     * @return Pages|Children
     */
    public function children()
    {
        if (is_a($this->children, Pages::class)) {
            return $this->children;
        }

        return $this->children = new Pages([]);
    }

    /**
     * Finds one or multiple children by id
     *
     * @param string ...$arguments
     * @return Pages|Children
     */
    public function find(...$arguments)
    {
        return $this->children()->find(...$arguments);
    }

    /**
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
     * @param Pages|Children|null $children
     * @return self
     */
    protected function setChildren(array $children = null)
    {
        $this->children = $children;
        return $this;
    }
}
