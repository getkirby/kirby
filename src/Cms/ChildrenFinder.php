<?php

namespace Kirby\Cms;

class ChildrenFinder extends PagesFinder
{

    protected $startAt;

    public function __construct($collection, $startAt)
    {
        $this->startAt    = $startAt;
        $this->collection = $collection;
    }

    public function findById($id)
    {
        $page = $this->collection()->get(ltrim($this->startAt . '/' . $id, '/'));

        if (!$page) {
            $page = $this->findByIdRecursive($id, $this->startAt);
        }

        return $page;
    }

}
