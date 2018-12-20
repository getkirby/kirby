<?php

namespace Kirby\Toolkit;

class CollectionGetterTest extends TestCase
{
    public function testGetMagic()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei'
        ]);

        $this->assertEquals('eins', $collection->one);
        $this->assertEquals('eins', $collection->ONE);
        $this->assertEquals(null, $collection->three);
    }

    public function testGet()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei'
        ]);

        $this->assertEquals('eins', $collection->get('one'));
        $this->assertEquals(null, $collection->get('three'));
        $this->assertEquals('default', $collection->get('three', 'default'));
    }

    public function testMagicMethods()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei'
        ]);

        $this->assertEquals('eins', $collection->one());
        $this->assertEquals('zwei', $collection->two());
        $this->assertEquals(null, $collection->three());
    }

    public function testGetAttribute()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei'
        ]);

        $this->assertEquals('eins', $collection->getAttribute($collection->toArray(), 'one'));
        $this->assertEquals(null, $collection->getAttribute($collection->toArray(), 'three'));

        $this->assertEquals('zwei', $collection->getAttribute($collection, 'two'));
        $this->assertEquals(null, $collection->getAttribute($collection, 'three'));
    }
}
