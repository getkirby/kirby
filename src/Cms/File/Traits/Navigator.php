<?php

namespace Kirby\Cms\File\Traits;

use Exception;

trait Navigator
{

    public function indexOf()
    {
        return $this->collection()->indexOf($this);
    }

    public function prev()
    {
        return $this->collection()->nth($this->indexOf() - 1);
    }

    public function hasPrev(): bool
    {
        return $this->prev() !== null;
    }

    public function next()
    {
        return $this->collection()->nth($this->indexOf() + 1);
    }

    public function hasNext(): bool
    {
        return $this->next() !== null;
    }

    public function isFirst(): bool
    {
        return $this->collection()->first()->is($this);
    }

    public function isLast(): bool
    {
        return $this->collection()->last()->is($this);
    }

}
