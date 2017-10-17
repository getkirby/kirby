<?php

namespace Kirby\Structure;

use Closure;
use Exception;
use Kirby\Collection\Collection as BaseCollection;

class Collection extends BaseCollection
{

    protected $dependencies = [];

    public function __construct(array $objects = [], array $dependencies = [])
    {
        $this->dependencies = $dependencies;
        parent::__construct($objects);
    }

    public function __set(string $id, $object)
    {

        if (is_array($object)) {
            $object = new Object($object, $this->dependencies);
        }

        if (!is_a($object, Object::class)) {
            throw new Exception('Invalid object in Structure collection');
        }

        // inject the collection for proper navigation
        $object->collection($this);

        return parent::__set($id, $object);

    }

    public function getAttribute($object, $attribute)
    {
        return (string)$object->$attribute();
    }

    public function toArray(Closure $map = null): array
    {
        return parent::toArray($map ?? function (Object $object) {
            return $object->toArray();
        });
    }

}
