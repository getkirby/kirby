<?php

namespace Kirby\Cms;

use Kirby\Collection\Finder;

class PagesFinder extends Finder
{

    public function findById($id)
    {
        $page = $this->collection()->get($id);

        if (!$page) {
            $page = $this->findByIdRecursive($id);
        }

        return $page;
    }

    public function findByIdRecursive($id, $startAt = null)
    {
        $path       = explode('/', $id);
        $collection = $this->collection();
        $item       = null;
        $query      = $startAt;

        foreach ($path as $key) {

            $query = ltrim($query . '/' . $key, '/');
            $item  = $collection->get($query) ?? null;

            if ($item === null) {
                return null;
            }

            $collection = $item->children();

        }

        return $item;
    }

    public function findByKey($key)
    {
        return $this->findById($key);
    }

}
