<?php

namespace Kirby\Cms;

use Exception;

class Pages extends Collection
{

    protected static $accept = Page::class;

    protected function finder()
    {
        return new PagesFinder($this);
    }

    public function visible(): self
    {
        return $this->filterBy('isVisible', '==', true);
    }

    public function invisible(): self
    {
        return $this->filterBy('isVisible', '==', false);
    }

}
