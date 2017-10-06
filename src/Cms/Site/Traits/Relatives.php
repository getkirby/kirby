<?php

namespace Kirby\Cms\Site\Traits;

use Kirby\Cms\Pages;
use Kirby\Cms\Page;

trait Relatives
{

    protected $children;

    public function children(): Pages
    {

        if (is_a($this->children, Pages::class)) {
            return $this->children;
        }

        if (is_array($this->children)) {
            return $this->children = new Pages($this->children);
        }

        return $this->children = new Pages($this->store->children());

    }

    public function pages(): Pages
    {
        return $this->children();
    }

    public function find(...$ids)
    {
        return $this->children()->find(...$ids);
    }

    public function child(array $attributes): Page
    {

        if (!isset($attributes['slug'])) {
            throw new Exception('Please provide a slug');
        }

        if (!isset($attributes['template'])) {
            throw new Exception('Please provide a template');
        }

        return new Page($attributes + [
            'id'         => $attributes['slug'],
            'url'        => $this->url() . '/' . $attributes['slug'],
            'root'       => $this->root() . '/' . $attributes['slug'],
            'site'       => $this->site(),
            'slug'       => $attributes['slug'],
            'template'   => $attributes['template'],
            'content'    => $attributes['content'] ?? []
        ]);
    }


}
