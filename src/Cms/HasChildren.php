<?php

namespace Kirby\Cms;

trait HasChildren
{

    public function find(...$arguments)
    {
        return $this->children()->find(...$arguments);
    }

}
