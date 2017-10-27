<?php

namespace Kirby\Cms;

class Structure extends Collection
{

    protected static $accept = StructureObject::class;

    public function __set(string $id, $object)
    {

        if (is_array($object)) {
            $object = new StructureObject([
                'content' => new Content($object, $this->parent),
                'id'      => $id,
                'parent'  => $this->parent
            ]);
        }

        if (!is_a($object, static::$accept)) {
            throw new Exception(sprintf('Invalid "%s" object in collection', static::$accept));
        }

        // inject the collection for proper navigation
        $object->set('collection', $this);

        return parent::__set($object->id(), $object);

    }


}
