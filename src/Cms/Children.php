<?php

namespace Kirby\Cms;

use Closure;

class Children extends Pages
{
    protected function finder()
    {
        return new ChildrenFinder($this, $this->parent ? $this->parent->id() : '');
    }
}
