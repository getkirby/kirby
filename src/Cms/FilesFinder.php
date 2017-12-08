<?php

namespace Kirby\Cms;

use Kirby\Collection\Finder;

class FilesFinder extends Finder
{
    protected $startAt;

    public function __construct($collection, $startAt)
    {
        $this->startAt    = $startAt;
        $this->collection = $collection;
    }

    public function findById($id)
    {
        return $this->collection()->get(ltrim($this->startAt . '/' . $id, '/'));
    }

    public function findByKey($key)
    {
        return $this->findById($key);
    }

}
