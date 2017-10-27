<?php

namespace Kirby\Cms;

trait HasSiblings {

    public function hasNext(): bool
    {
        return $this->next() !== null;
    }

    public function hasPrev(): bool
    {
        return $this->prev() !== null;
    }

    public function indexOf(): int
    {
        return $this->collection()->indexOf($this);
    }

    public function isFirst(): bool
    {
        return $this->collection()->first()->is($this);
    }

    public function isLast(): bool
    {
        return $this->collection()->last()->is($this);
    }

    public function next()
    {
        if ($collection = $this->collection()) {
            return $collection->nth($this->indexOf() + 1);
        }

        return null;
    }

    public function nextAll()
    {
        if ($collection = $this->collection()) {
            return $collection->slice($this->indexOf() + 1);
        }

        return null;
    }

    public function prev()
    {
        if ($collection = $this->collection()) {
            return $collection->nth($this->indexOf() - 1);
        }

        return null;
    }

    public function prevAll()
    {
        if ($collection = $this->collection()) {
            return $collection->slice(0, $this->indexOf());
        }

        return null;
    }

    public function siblings()
    {
        if ($parent = $this->parent()) {
            return $parent->children();
        }

        return $this->site()->children();
    }

}
