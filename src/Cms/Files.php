<?php

namespace Kirby\Cms;

use Exception;

class Files extends Collection
{

    protected static $accept = File::class;

    protected $parent;

    public function __construct($files = [], $parent = null)
    {
        $this->parent = $parent;
        parent::__construct($files);
    }

    protected function finder()
    {
        if (is_a($this->parent, Page::class) === true) {
            return new FilesFinder($this, $this->parent->id());
        }

        return new FilesFinder($this, null);
    }

}
