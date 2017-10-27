<?php

namespace Kirby\Cms;

use Exception;

class Files extends Collection
{

    protected static $accept = File::class;

    protected function finder()
    {
        if (is_a($this->parent, Page::class) === true) {
            return new FilesFinder($this, $this->parent->id());
        }

        return new FilesFinder($this, null);
    }

}
