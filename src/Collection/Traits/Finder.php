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

    /**
     * Checks if an element is in the Object by key.
     *
     * @param  mixed  $key
     * @return boolean
     */
    public function has($key): bool
    {
        return isset($this->data[$key]);
    }

}

