<?php

namespace Kirby\Cms;

class CollectionTest extends TestCase
{

    public function testWithValidObjects()
    {
        $collection = new Collection([
            $a = new Object(['id' => 'a', 'name' => 'a']),
            $b = new Object(['id' => 'b', 'name' => 'b']),
            $c = new Object(['id' => 'c', 'name' => 'c'])
        ]);

        $this->assertEquals($a, $collection->first());
        $this->assertEquals($c, $collection->last());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid object in collection. Accepted: "Kirby\Cms\Object"
     */
    public function testWithInvalidStringItems()
    {
        $collection = new Collection([
            'a',
            'b',
            'c'
        ]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid object in collection. Accepted: "Kirby\Cms\Object"
     */
    public function testWithInvalidArrayItems()
    {
        $collection = new Collection([
            ['a' => 'a'],
            ['b' => 'b'],
            ['c' => 'c'],
        ]);
    }

    public function testGetAttribute()
    {
        $object     = new Object(['name' => 'a']);
        $collection = new Collection();
        $value      = $collection->getAttribute($object, 'name');

        $this->assertEquals('a', $value);
    }

    public function testGetAttributeWithField()
    {
        $object = new Object([
            'name' => new Field('name', 'a')
        ]);

        $collection = new Collection();
        $value      = $collection->getAttribute($object, 'name');

        $this->assertEquals('a', $value);
    }

    public function testIndexOfWithObject()
    {
        $collection = new Collection([
            $a = new Object(['id' => 'a']),
            $b = new Object(['id' => 'b']),
            $c = new Object(['id' => 'c'])
        ]);

        $this->assertEquals(0, $collection->indexOf($a));
        $this->assertEquals(1, $collection->indexOf($b));
        $this->assertEquals(2, $collection->indexOf($c));
    }

    public function testIndexOfWithString()
    {
        $collection = new Collection([
            new Object(['id' => 'a']),
            new Object(['id' => 'b']),
            new Object(['id' => 'c'])
        ]);

        $this->assertEquals(0, $collection->indexOf('a'));
        $this->assertEquals(1, $collection->indexOf('b'));
        $this->assertEquals(2, $collection->indexOf('c'));
    }

    public function testNotWithObjects()
    {
        $collection = new Collection([
            $a = new Object(['id' => 'a']),
            $b = new Object(['id' => 'b']),
            $c = new Object(['id' => 'c'])
        ]);

        $result = $collection->not($a);

        $this->assertCount(2, $result);
        $this->assertEquals($b, $result->first());
        $this->assertEquals($c, $result->last());

        $result = $collection->not($a, $b);

        $this->assertCount(1, $result);
        $this->assertEquals($c, $result->first());
        $this->assertEquals($c, $result->last());
    }

    public function testNotWithString()
    {
        $collection = new Collection([
            $a = new Object(['id' => 'a']),
            $b = new Object(['id' => 'b']),
            $c = new Object(['id' => 'c'])
        ]);

        $result = $collection->not('a');

        $this->assertCount(2, $result);
        $this->assertEquals($b, $result->first());
        $this->assertEquals($c, $result->last());

        $result = $collection->not('a', 'b');

        $this->assertCount(1, $result);
        $this->assertEquals($c, $result->first());
        $this->assertEquals($c, $result->last());
    }

    public function testPaginate()
    {
        $collection = new Collection([
            $a = new Object(['id' => 'a']),
            $b = new Object(['id' => 'b']),
            $c = new Object(['id' => 'c'])
        ]);

        // page: 1
        $result = $collection->paginate(1);

        $this->assertCount(1, $result);
        $this->assertEquals($a, $result->first());
        $this->assertEquals($a, $result->last());

        // page: 2
        $result = $collection->paginate(1, 2);

        $this->assertCount(1, $result);
        $this->assertEquals($b, $result->first());
        $this->assertEquals($b, $result->last());

        // page: 3
        $result = $collection->paginate(1, 3);

        $this->assertCount(1, $result);
        $this->assertEquals($c, $result->first());
        $this->assertEquals($c, $result->last());
    }

    public function testToArray()
    {
        $schema = [
            'id' => [
                'type' => 'string'
            ]
        ];

        $collection = new Collection([
            new Object(['id' => 'a'], $schema),
            new Object(['id' => 'b'], $schema),
            new Object(['id' => 'c'], $schema)
        ]);

        $array = $collection->toArray();

        $this->assertEquals($array, [
            'a' => [
                'id' => 'a'
            ],
            'b' => [
                'id' => 'b'
            ],
            'c' => [
                'id' => 'c'
            ]
        ]);
    }

    public function testToArrayWithCallback()
    {
        $collection = new Collection([
            new Object(['id' => 'a']),
            new Object(['id' => 'b']),
            new Object(['id' => 'c'])
        ]);

        $array = $collection->toArray(function ($object) {
            return $object->id();
        });

        $this->assertEquals($array, [
            'a' => 'a',
            'b' => 'b',
            'c' => 'c'
        ]);
    }

}
