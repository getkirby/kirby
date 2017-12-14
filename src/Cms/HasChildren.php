<?php

namespace Kirby\Cms;

trait HasChildren
{

    public function find(...$arguments)
    {
        return $this->children()->find(...$arguments);
    }

    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

}
