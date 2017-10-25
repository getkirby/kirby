<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Kirby\Collection\Collection as BaseCollection;

class Collection extends BaseCollection
{

    protected static $accept = Object::class;

    public function indexOf($object)
    {
        return array_search($object->id(), $this->keys());
    }

    public function getAttribute($object, $prop)
    {
        return (string)$object->$prop();
    }

    public function __set(string $id, $object)
    {

        if (!is_a($object, static::$accept)) {
            throw new Exception(sprintf('Invalid "%s" object in collection', static::$accept));
        }

        // inject the collection for proper navigation
        $object->set('collection', $this);

        return parent::__set($object->id(), $object);

    }

    public function toArray(Closure $map = null): array
    {
        return parent::toArray($map ?? function (Object $object) {
            return $object->toArray();
        });
    }

}
