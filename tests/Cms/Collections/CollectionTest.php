<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Obj;
use stdClass;

class MockObject extends Model
{
    protected $id;
    protected $group;

    public function __construct(array $props = [])
    {
        $this->id    = $props['id'];
        $this->group = $props['group'] ?? null;
    }

    public function id()
    {
        return $this->id;
    }

    public function group()
    {
        return $this->group;
    }

    public function toArray(): array
    {
        return ['id' => $this->id];
    }
}

class CollectionTest extends TestCase
{
    public function testCollectionMethods()
    {
        $kirby = $this->kirby([
            'collectionMethods' => [
                'test' => function () {
                    return 'collection test';
                }
            ],
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $this->assertSame('collection test', (new Collection())->test());
        $this->assertSame('collection test', $kirby->site()->children()->test());

        Pages::$methods['test'] = function () {
            return 'pages test';
        };

        $this->assertSame('collection test', (new Collection())->test());
        $this->assertSame('pages test', $kirby->site()->children()->test());

        Collection::$methods = [];
        Pages::$methods = [];
    }

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

    public function testWithArray()
    {
        $collection = new Collection([
            $a = ['id' => 'a', 'name' => 'a'],
            $b = ['id' => 'b', 'name' => 'b'],
            $c = ['id' => 'c', 'name' => 'c']
        ]);

        $this->assertEquals($a, $collection->first());
        $this->assertEquals($c, $collection->last());
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

    public function testAppend()
    {
        $a = new MockObject(['id' => 'a', 'name' => 'A']);
        $b = new MockObject(['id' => 'b', 'name' => 'B']);
        $c = new Obj(['id' => 'c', 'name' => 'C']);
        $d = new stdClass();
        $d->id = 'd';
        $d->name = 'D';

        // with custom keys
        $collection = new Collection();
        $collection = $collection->append('key-a', $a);
        $collection = $collection->append('key-b', $b);
        $collection = $collection->append('key-c', $c);
        $collection = $collection->append('key-d', $d);
        $collection = $collection->append('key-e', 'a simple string');

        $this->assertEquals(['key-a', 'key-b', 'key-c', 'key-d', 'key-e'], $collection->keys());
        $this->assertEquals([$a, $b, $c, $d, 'a simple string'], $collection->values());

        // with automatic keys
        $collection = new Collection();
        $collection = $collection->append($a);
        $collection = $collection->append($b);
        $collection = $collection->append($c);
        $collection = $collection->append($d);
        $collection = $collection->append('a simple string');

        $this->assertEquals(['a', 'b', 'c', 0, 1], $collection->keys());
        $this->assertEquals([$a, $b, $c, $d, 'a simple string'], $collection->values());
    }

    public function testGroup()
    {
        $collection = new Collection([
            $a = new MockObject(['id' => 'a', 'group' => 'a']),
            $b = new MockObject(['id' => 'b', 'group' => 'a']),
            $c = new MockObject(['id' => 'c', 'group' => 'b']),
        ]);

        $groups = $collection->group('group');

        $this->assertInstanceOf(Collection::class, $groups);
        $this->assertCount(2, $groups);

        $groupA = $groups->first();
        $groupB = $groups->last();

        $this->assertCount(2, $groupA);
        $this->assertCount(1, $groupB);
    }

    public function testGroupWithInvalidKey()
    {
        $collection = new Collection([
            $a = new MockObject(['id' => 'a', 'group' => 'a']),
            $b = new MockObject(['id' => 'b', 'group' => 'a']),
            $c = new MockObject(['id' => 'c', 'group' => 'b']),
        ]);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Can only group by string values or by providing a callback function');

        $collection->group(1);
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

    public function testNotWithCollection()
    {
        $collection = new Collection([
            new MockObject(['id' => 'a']),
            new MockObject(['id' => 'b']),
            new MockObject(['id' => 'c'])
        ]);

        $not = $collection->find('a', 'c');

        $result = $collection->not($not);
        $this->assertCount(1, $result);
        $this->assertEquals('b', $result->first()->id());
    }

    public function testNotWithSimpleArray()
    {
        $collection = new Collection([
            new MockObject(['id' => 'a']),
            new MockObject(['id' => 'b']),
            new MockObject(['id' => 'c'])
        ]);

        $not = ['a', 'c', 'non-exists'];

        $result = $collection->not($not);
        $this->assertCount(1, $result);
        $this->assertSame('b', $result->first()->id());
    }

    public function testNotWithCollectionsArray()
    {
        $collection = new Collection([
            new MockObject(['id' => 'a']),
            new MockObject(['id' => 'b']),
            new MockObject(['id' => 'c'])
        ]);

        $not = [
            new Collection([
                new MockObject(['id' => 'a']),
                new MockObject(['id' => 'non-exists'])
            ]),
            new Collection([
                new MockObject(['id' => 'b'])
            ])
        ];

        $result = $collection->not($not);
        $this->assertCount(1, $result);
        $this->assertSame('c', $result->first()->id());
    }

    public function testNotWithObjectsArray()
    {
        $collection = new Collection([
            new MockObject(['id' => 'a']),
            new MockObject(['id' => 'b']),
            new MockObject(['id' => 'c'])
        ]);

        $not = [
            new MockObject(['id' => 'b']),
            new MockObject(['id' => 'c']),
            new MockObject(['id' => 'non-exists']),
        ];

        $result = $collection->not($not);
        $this->assertCount(1, $result);
        $this->assertSame('a', $result->first()->id());
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

    public function testPrepend()
    {
        $a = new MockObject(['id' => 'a', 'name' => 'A']);
        $b = new MockObject(['id' => 'b', 'name' => 'B']);
        $c = new Obj(['id' => 'c', 'name' => 'C']);
        $d = new stdClass();
        $d->id = 'd';
        $d->name = 'D';

        // with custom keys
        $collection = new Collection();
        $collection = $collection->prepend('key-a', $a);
        $collection = $collection->prepend('key-b', $b);
        $collection = $collection->prepend('key-c', $c);
        $collection = $collection->prepend('key-d', $d);
        $collection = $collection->prepend('key-e', 'a simple string');

        $this->assertEquals(['key-e', 'key-d', 'key-c', 'key-b', 'key-a'], $collection->keys());
        $this->assertEquals(['a simple string', $d, $c, $b, $a], $collection->values());

        // with automatic keys
        $collection = new Collection();
        $collection = $collection->prepend($a);
        $collection = $collection->prepend($b);
        $collection = $collection->prepend($c);
        $collection = $collection->prepend($d);
        $collection = $collection->prepend('a simple string');

        $this->assertEquals([0, 1, 'c', 'b', 'a'], $collection->keys());
        $this->assertEquals(['a simple string', $d, $c, $b, $a], $collection->values());
    }

    public function testQuerySearch()
    {
        $collection = new Collection([
            new Page(['slug' => 'project-a']),
            new Page(['slug' => 'project-b']),
            new Page(['slug' => 'project-c'])
        ]);

        // simple
        $result = $collection->query([
            'search' => 'project-b'
        ]);

        $this->assertCount(1, $result);
        $this->assertEquals('project-b', $result->first()->id());

        // with options array
        $result = $collection->query([
            'search' => [
                'query' => 'project-b'
            ]
        ]);

        $this->assertCount(1, $result);
        $this->assertEquals('project-b', $result->first()->id());
    }

    public function testQueryPagination()
    {
        $collection = new Collection([
            new Page(['slug' => 'project-a']),
            new Page(['slug' => 'project-b']),
            new Page(['slug' => 'project-c'])
        ]);

        $result = $collection->query([
            'paginate' => 1
        ]);

        $this->assertCount(1, $result);
        $this->assertEquals('project-a', $result->first()->id());
        $this->assertEquals(3, $result->pagination()->pages());
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
