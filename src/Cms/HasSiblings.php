<?php

namespace Kirby\Cms;

trait HasSiblings {

    public function indexOf(): int
    {
        return $this->collection()->indexOf($this);
    }

    public function siblings()
    {
        if ($parent = $this->parent()) {
            return $parent->children();
        }

        return $this->site()->children();
    }

    public function prev()
    {
        if ($collection = $this->collection()) {
            return $collection->nth($this->indexOf() - 1);
        }

        return null;
    }

    public function hasPrev(): bool
    {
        return $this->prev() !== null;
    }

    public function next()
    {
        if ($collection = $this->collection()) {
            return $collection->nth($this->indexOf() + 1);
        }

        return null;
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
