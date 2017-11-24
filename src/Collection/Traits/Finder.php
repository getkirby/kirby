<?php

namespace Kirby\Collection\Traits;

use Kirby\Collection\Finder as FinderClass;

trait Finder
{

    protected function finder()
    {
        return new FinderClass($this);
    }

    public function find(...$arguments)
    {
        return $this->finder()->find(...$arguments);
    }

    public function findBy(string $key, $value)
    {
        return $this->finder()->findBy($key, $value);
    }

}

