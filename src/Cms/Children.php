<?php

namespace Kirby\Cms;

use Closure;

class Children extends Pages
{

    protected $parent;

    public function __construct($children = [], Page $parent)
    {
        $this->parent = $parent;
        parent::__construct($children);
    }

    protected function finder()
    {
        return new ChildrenFinder($this, $this->parent ? $this->parent->id() : '');
    }

}
