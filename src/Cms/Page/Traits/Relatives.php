<?php

namespace Kirby\Cms\Page\Traits;

use Exception;
use Kirby\Cms\Page;
use Kirby\Cms\Page\Children;
use Kirby\Cms\Pages;

trait Relatives
{

    protected $children;
    protected $collection;

    public function collection(Pages $collection = null)
    {
        if ($collection === null) {
            if (is_a($this->collection, Pages::class)) {
                return $this->collection;
            }

            return $this->collection = $this->parent()->children();
        }

        $this->collection = $collection;
        return $this;
    }

    public function children(): Children
    {

        if (is_a($this->children, Children::class)) {
            return $this->children;
        }

        if (isset($this->attributes['children']) && is_array($this->attributes['children'])) {
            return $this->children = new Children($this->attributes['children'], $this);
        }

        return $this->children = new Children($this->store->children(), $this);

    }

    public function find(...$ids)
    {
        return $this->children()->find(...$ids);
    }

    public function siblings(): Children
    {
        return $this->parent()->children();
    }

    public function parent()
    {
        return $this->attributes['parent'] ?? null;
    }

    public function site()
    {
        return $this->attributes['site'] ?? null;
    }

    public function parents(): Pages
    {

        $parents = new Pages;
        $page    = $this->parent();

        while ($page !== null) {
            $parents->append($page->id(), $page);
            $page = $page->parent();
        }

        return $parents;

    }

    public function child(array $attributes): self
    {

        if (!isset($attributes['slug'])) {
            throw new Exception('Please provide a slug');
        }

        if (!isset($attributes['template'])) {
            throw new Exception('Please provide a template');
        }

        return new static($attributes + [
            'id'         => $this->id() . '/' . $attributes['slug'],
            'url'        => $this->url() . '/' . $attributes['slug'],
            'root'       => $this->root() . '/' . $attributes['slug'],
            'parent'     => $this,
            'site'       => $this->site(),
            'slug'       => $attributes['slug'],
            'template'   => $attributes['template'],
            'content'    => $attributes['content'] ?? []
        ]);
    }

}
