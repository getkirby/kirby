<?php

namespace Kirby\Cms;

use ReflectionClass;

class CollectionTestCase extends TestCase
{

    public $collection = null;
    public $collectionType = null;

    public function collection($name = null)
    {
        return $this->kirby()->collection($name ?? $this->collection);
    }

    public function collectionRoot(): string
    {
        return $this->kirby()->root('collections') . '/' . $this->collectionName() . '.php';
    }

    public function collectionName(): string
    {
        if ($this->collection !== null) {
            return $this->collection;
        }

        $reflect   = new ReflectionClass($this);
        $className = $reflect->getShortName();

        return strtolower(str_replace('CollectionTest', '', $className));
    }

    public function testCollectionType()
    {
        if ($this->collectionType === null) {
            $this->markTestSkipped();
        }

        $this->assertInstanceOf($this->collectionType, $this->collection());
    }

}
