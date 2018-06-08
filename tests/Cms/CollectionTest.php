<?php

namespace Kirby\Cms;

class MockObject extends Model
{

    public function __construct(array $props = [])
    {
        $this->id = $props['id'];
    }

    public function id()
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return ['id' => $this->id];
    }

}

class CollectionTest extends TestCase
{

    public function testWithValidObjects()
    {
        $collection = new Collection([
            $a = new MockObject(['id' => 'a', 'name' => 'a']),
            $b = new MockObject(['id' => 'b', 'name' => 'b']),
            $c = new MockObject(['id' => 'c', 'name' => 'c'])
        ]);

        $this->assertEquals($a, $collection->first());
        $this->assertEquals($c, $collection->last());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid object in collection. Accepted: Kirby\Cms\Model
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
     * @expectedExceptionMessage Invalid object in collection. Accepted: Kirby\Cms\Model
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
        $object     = new MockObject(['id' => 'a']);
        $collection = new Collection();
        $value      = $collection->getAttribute($object, 'id');

        $this->assertEquals('a', $value);
    }

    public function testGetAttributeWithField()
    {
        $object = new MockObject([
            'id' => new Field(null, 'id', 'a')
        ]);

        $collection = new Collection();
        $value      = $collection->getAttribute($object, 'id');

        $this->assertEquals('a', $value);
    }

    public function testIndexOfWithObject()
    {
        $collection = new Collection([
            $a = new MockObject(['id' => 'a']),
            $b = new MockObject(['id' => 'b']),
            $c = new MockObject(['id' => 'c'])
        ]);

        $this->assertEquals(0, $collection->indexOf($a));
        $this->assertEquals(1, $collection->indexOf($b));
        $this->assertEquals(2, $collection->indexOf($c));
    }

    public function testIndexOfWithString()
    {
        $collection = new Collection([
            new MockObject(['id' => 'a']),
            new MockObject(['id' => 'b']),
            new MockObject(['id' => 'c'])
        ]);

        $this->assertEquals(0, $collection->indexOf('a'));
        $this->assertEquals(1, $collection->indexOf('b'));
        $this->assertEquals(2, $collection->indexOf('c'));
    }

    public function testNotWithObjects()
    {
        $collection = new Collection([
            $a = new MockObject(['id' => 'a']),
            $b = new MockObject(['id' => 'b']),
            $c = new MockObject(['id' => 'c'])
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
            $a = new MockObject(['id' => 'a']),
            $b = new MockObject(['id' => 'b']),
            $c = new MockObject(['id' => 'c'])
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
            $a = new MockObject(['id' => 'a']),
            $b = new MockObject(['id' => 'b']),
            $c = new MockObject(['id' => 'c'])
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
        $collection = new Collection([
            new MockObject(['id' => 'a']),
            new MockObject(['id' => 'b']),
            new MockObject(['id' => 'c'])
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
            new MockObject(['id' => 'a']),
            new MockObject(['id' => 'b']),
            new MockObject(['id' => 'c'])
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
