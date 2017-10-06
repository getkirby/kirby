<?php

namespace Kirby\Cms\Page;

use Closure;

use Kirby\Cms\Page;
use Kirby\Cms\Page\Children\Finder;
use Kirby\Cms\Pages;

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
        return new Finder($this, $this->parent ? $this->parent->id() : '');
    }

}
