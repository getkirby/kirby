<?php

namespace Kirby\Cms\Page\Children;

use Kirby\Cms\Pages\Finder as BaseFinder;

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
        $page = $this->collection()->get(ltrim($this->startAt . '/' . $id, '/'));

        if (!$page) {
            $page = $this->findByIdRecursive($id, $this->startAt);
        }

        return $page;
    }
}
