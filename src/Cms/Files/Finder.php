<?php

namespace Kirby\Cms\Files;

use Kirby\Collection\Finder as BaseFinder;

class Finder extends BaseFinder
{
    protected $startAt;

    public function __construct($collection, $startAt)
    {
        $this->startAt    = $startAt;
        $this->collection = $collection;
    }

    public function findById(string $id)
    {
        return $this->collection()->get(ltrim($this->startAt . '/' . $id, '/'));
    }

    public function findByKey(string $key)
    {
        return $this->findById($key);
    }

}
