<?php

namespace Kirby\Collection;

use Exception;

class Finder
{

    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function findBy($attribute, $value)
    {
        foreach ($this->collection as $key => $item) {
            if ($this->collection->getAttribute($item, $attribute) == $value) {
                return $item;
            }
        }
        return null;
    }

    public function find(...$keys)
    {
        if (count($keys) === 1) {
            return $this->findByKey($keys[0]);
        }

        $result = [];

        foreach ($keys as $key) {
            if ($item = $this->findByKey($key)) {
                $result[$key] = $item;
            }
        }

        $collection = clone $this->collection;
        return $collection->data($result);
    }

    public function findByKey($key)
    {
        return $this->collection()->get($key);
    }

}
