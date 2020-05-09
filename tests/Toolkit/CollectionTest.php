<?php

namespace Kirby\Toolkit;

class StringObject
{
    protected $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }
}

class CollectionTest extends TestCase
{
    protected $collection;
    protected $sampleData;

    public function setUp(): void
    {
        $this->sampleData = [
            'first'  => 'My first element',
            'second' => 'My second element',
            'third'  => 'My third element',
        ];

        $this->collection = new Collection($this->sampleData);
    }

    public function test__debuginfo()
    {
        $collection = new Collection(['a' => 'A', 'b' => 'B']);
        $this->assertEquals(['a', 'b'], $collection->__debugInfo());
    }

    public function assertIsUntouched()
    {
        // the original collection must to be untouched
        $this->assertEquals($this->sampleData, $this->collection->toArray());
    }

    public function testAppend()
    {
        // simple
        $collection = new Collection();
        $collection = $collection->append('a');
        $collection = $collection->append('b');
        $collection = $collection->append('c');

        $this->assertEquals([0, 1, 2], $collection->keys());
        $this->assertEquals(['a', 'b', 'c'], $collection->values());

        // with key
        $collection = new Collection();
        $collection = $collection->append('a', 'A');
        $collection = $collection->append('b', 'B');
        $collection = $collection->append('c', 'C');

        $this->assertEquals(['a', 'b', 'c'], $collection->keys());
        $this->assertEquals(['A', 'B', 'C'], $collection->values());

        // with too many params
        $collection = new Collection();
        $collection = $collection->append('a', 'A', 'ignore this');
        $collection = $collection->append('b', 'B', 'ignore this');
        $collection = $collection->append('c', 'C', 'ignore this');

        $this->assertEquals(['a', 'b', 'c'], $collection->keys());
        $this->assertEquals(['A', 'B', 'C'], $collection->values());
    }

    public function testCaseSensitive()
    {
        $normalCollection = new Collection([
            'lowercase' => 'test1',
            'UPPERCASE' => 'test2',
            'MiXeD'     => 'test3'
        ]);
        $normalCollection->set('AnOtHeR', 'test4');

        $this->assertSame([
            'lowercase' => 'test1',
            'uppercase' => 'test2',
            'mixed'     => 'test3',
            'another'   => 'test4'
        ], $normalCollection->data());
        $this->assertSame('test1', $normalCollection->get('lowercase'));
        $this->assertSame('test2', $normalCollection->get('UPPERCASE'));
        $this->assertSame('test3', $normalCollection->get('MiXeD'));
        $this->assertSame('test4', $normalCollection->get('AnOtHeR'));
        $this->assertSame('test1', $normalCollection->get('LowerCase'));
        $this->assertSame('test2', $normalCollection->get('uppercase'));
        $this->assertSame('test3', $normalCollection->get('mIxEd'));
        $this->assertSame('test4', $normalCollection->get('another'));

        $sensitiveCollection = new Collection([
            'lowercase' => 'test1',
            'UPPERCASE' => 'test2',
            'MiXeD'     => 'test3'
        ], true);
        $sensitiveCollection->set('AnOtHeR', 'test4');

        $this->assertSame([
            'lowercase' => 'test1',
            'UPPERCASE' => 'test2',
            'MiXeD'     => 'test3',
            'AnOtHeR'   => 'test4'
        ], $sensitiveCollection->data());
        $this->assertSame('test1', $sensitiveCollection->get('lowercase'));
        $this->assertSame('test2', $sensitiveCollection->get('UPPERCASE'));
        $this->assertSame('test3', $sensitiveCollection->get('MiXeD'));
        $this->assertSame('test4', $sensitiveCollection->get('AnOtHeR'));
        $this->assertNull($sensitiveCollection->get('Lowercase'));
        $this->assertNull($sensitiveCollection->get('uppercase'));
        $this->assertNull($sensitiveCollection->get('mixed'));
        $this->assertNull($sensitiveCollection->get('another'));
    }

    public function testCount()
    {
        $this->assertEquals(3, $this->collection->count());
        $this->assertEquals(3, count($this->collection));
    }

    public function testFilter()
    {
        $func = function ($element) {
            return ($element == 'My second element') ? true : false;
        };

        $filtered = $this->collection->filter($func);

        $this->assertEquals('My second element', $filtered->first());
        $this->assertEquals('My second element', $filtered->last());
        $this->assertEquals(1, $filtered->count());
        $this->assertIsUntouched();
    }

    public function testFirst()
    {
        $this->assertEquals('My first element', $this->collection->first());
    }

    public function testFlip()
    {
        $this->assertEquals(array_reverse($this->sampleData, true), $this->collection->flip()->toArray());
        $this->assertEquals($this->sampleData, $this->collection->flip()->flip()->toArray());
        $this->assertIsUntouched();
    }

    public function testGetAttributeFromArray()
    {
        $collection = new Collection([
            'a' => [
                'username' => 'Homer',
                'tags' => 'simpson, male'
            ],
            'b' => [
                'username' => 'Marge',
                'tags' => 'simpson, female'
            ],
        ]);

        $this->assertEquals('Homer', $collection->getAttribute($collection->first(), 'username'));
        $this->assertEquals('Marge', $collection->getAttribute($collection->last(), 'username'));

        // split
        $this->assertEquals(['simpson', 'male'], $collection->getAttribute($collection->first(), 'tags', true));
        $this->assertEquals(['simpson', 'female'], $collection->getAttribute($collection->last(), 'tags', true));
    }

    public function testGetAttributeFromObject()
    {
        $collection = new Collection([
            'a' => new Obj([
                'username' => 'Homer'
            ]),
            'b' => new Obj([
                'username' => 'Marge'
            ]),
        ]);

        $this->assertEquals('Homer', $collection->getAttribute($collection->first(), 'username'));
        $this->assertEquals('Marge', $collection->getAttribute($collection->last(), 'username'));
    }

    public function testGetters()
    {
        $this->assertEquals('My first element', $this->collection->first);
        $this->assertEquals('My second element', $this->collection->second);
        $this->assertEquals('My third element', $this->collection->third);

        $this->assertEquals('My first element', $this->collection->first());
        $this->assertEquals('My second element', $this->collection->second());
        $this->assertEquals('My third element', $this->collection->third());

        $this->assertEquals('My first element', $this->collection->get('first'));
        $this->assertEquals('My second element', $this->collection->get('second'));
        $this->assertEquals('My third element', $this->collection->get('third'));
    }

    public function testGroup()
    {
        $collection = new Collection();

        $collection->user1 = [
            'username' => 'peter',
            'group'    => 'admin'
        ];

        $collection->user2 = [
            'username' => 'paul',
            'group'    => 'admin'
        ];

        $collection->user3 = [
            'username' => 'mary',
            'group'    => 'client'
        ];

        $groups = $collection->group(function ($item) {
            return $item['group'];
        });

        $this->assertEquals(2, $groups->admin()->count());
        $this->assertEquals(1, $groups->client()->count());

        $firstAdmin = $groups->admin()->first();
        $this->assertEquals('peter', $firstAdmin['username']);
    }

    public function testGroupWithInvalidKey()
    {
        $collection = new Collection(['a' => 'A']);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid grouping value for key: a');

        $collection->group(function ($item) {
            return false;
        });
    }

    public function testGroupByArray()
    {
        $collection = new Collection(['a' => 'A']);

        $this->expectException('Exception');
        $this->expectExceptionMessage('You cannot group by arrays or objects');

        $collection->group(function ($item) {
            return ['a' => 'b'];
        });
    }

    public function testGroupByObject()
    {
        $collection = new Collection(['a' => 'A']);

        $this->expectException('Exception');
        $this->expectExceptionMessage('You cannot group by arrays or objects');

        $collection->group(function ($item) {
            return new Obj(['a' => 'b']);
        });
    }

    public function testGroupByStringObject()
    {
        $collection = new Collection();

        $collection->user1 = [
            'username' => 'peter',
            'group'    => new StringObject('admin')
        ];

        $collection->user2 = [
            'username' => 'paul',
            'group'    => new StringObject('admin')
        ];

        $collection->user3 = [
            'username' => 'mary',
            'group'    => new StringObject('client')
        ];

        $groups = $collection->group(function ($item) {
            return $item['group'];
        });

        $this->assertEquals(2, $groups->admin()->count());
        $this->assertEquals(1, $groups->client()->count());

        $firstAdmin = $groups->admin()->first();
        $this->assertEquals('peter', $firstAdmin['username']);
    }

    public function testGroupBy()
    {
        $collection = new Collection();

        $collection->user1 = [
            'username' => 'peter',
            'group'    => 'admin'
        ];

        $collection->user2 = [
            'username' => 'paul',
            'group'    => 'admin'
        ];

        $collection->user3 = [
            'username' => 'mary',
            'group'    => 'client'
        ];

        $groups = $collection->groupBy('group');

        $this->assertEquals(2, $groups->admin()->count());
        $this->assertEquals(1, $groups->client()->count());

        $firstAdmin = $groups->admin()->first();
        $this->assertEquals('peter', $firstAdmin['username']);
    }

    public function testGroupByWithInvalidKey()
    {
        $collection = new Collection(['a' => 'A']);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Cannot group by non-string values. Did you mean to call group()?');

        $collection->groupBy(1);
    }

    public function testIndexOf()
    {
        $this->assertEquals(1, $this->collection->indexOf('My second element'));
    }

    public function testIntersection()
    {
        $collection1 = new Collection([
            'a' => $a = new StringObject('a'),
            'b' => $b = new StringObject('b'),
            'c' => $c = new StringObject('c')
        ]);

        $collection2 = new Collection([
            'c' => $c,
            'd' => $d = new StringObject('d'),
            'b' => $b
        ]);

        $collection3 = new Collection([
            'd' => $d,
            'e' => $e = new StringObject('e')
        ]);

        // 1 with 2
        $result = $collection1->intersection($collection2);

        $this->assertCount(2, $result);
        $this->assertEquals($b, $result->first());
        $this->assertEquals($c, $result->last());

        // 2 with 1
        $result = $collection2->intersection($collection1);

        $this->assertCount(2, $result);
        $this->assertEquals($c, $result->first());
        $this->assertEquals($b, $result->last());

        // 1 with 3
        $result = $collection1->intersection($collection3);

        $this->assertCount(0, $result);

        // 3 with 2
        $result = $collection3->intersection($collection2);

        $this->assertCount(1, $result);
        $this->assertEquals($d, $result->first());
    }

    public function testIntersects()
    {
        $collection1 = new Collection([
            'a' => $a = new StringObject('a'),
            'b' => $b = new StringObject('b'),
            'c' => $c = new StringObject('c')
        ]);

        $collection2 = new Collection([
            'c' => $c,
            'd' => $d = new StringObject('d'),
            'b' => $b
        ]);

        $collection3 = new Collection([
            'd' => $d,
            'e' => $e = new StringObject('e')
        ]);

        // 1 with 2
        $this->assertTrue($collection1->intersects($collection2));

        // 2 with 1
        $this->assertTrue($collection2->intersects($collection1));

        // 1 with 3
        $this->assertFalse($collection1->intersects($collection3));

        // 3 with 2
        $this->assertTrue($collection3->intersects($collection2));
    }

    public function testIsEmpty()
    {
        $collection = new Collection([
            [ 'name'  => 'Bastian' ],
            [ 'name' => 'Nico' ]
        ]);

        $this->assertTrue($collection->isNotEmpty());
        $this->assertFalse($collection->isEmpty());
    }

    public function testIsEven()
    {
        $collection = new Collection(['a' => 'a']);
        $this->assertFalse($collection->isEven());

        $collection = new Collection(['a' => 'a', 'b' => 'b']);
        $this->assertTrue($collection->isEven());
    }

    public function testIsNotEmpty()
    {
        $collection = new Collection([]);

        $this->assertTrue($collection->isEmpty());
        $this->assertFalse($collection->isNotEmpty());
    }

    public function testIsOdd()
    {
        $collection = new Collection(['a' => 'a']);
        $this->assertTrue($collection->isOdd());

        $collection = new Collection(['a' => 'a', 'b' => 'b']);
        $this->assertFalse($collection->isOdd());
    }

    public function testIsset()
    {
        $this->assertTrue(isset($this->collection->first));
        $this->assertFalse(isset($this->collection->super));
    }

    public function testKeyOf()
    {
        $this->assertEquals('second', $this->collection->keyOf('My second element'));
    }

    public function testKeys()
    {
        $this->assertEquals(['first', 'second', 'third'], $this->collection->keys());
    }

    public function testLast()
    {
        $this->assertEquals('My third element', $this->collection->last());
    }

    public function testNextAndPrev()
    {
        $this->assertEquals('My second element', $this->collection->next());
        $this->assertEquals('My third element', $this->collection->next());
        $this->assertEquals('My second element', $this->collection->prev());
    }

    public function testNotAndWithout()
    {
        // remove elements
        $this->assertEquals('My second element', $this->collection->not('first')->first());
        $this->assertEquals(1, $this->collection->not('second')->not('third')->count());
        $this->assertEquals(0, $this->collection->not('first', 'second', 'third')->count());

        // also check the alternative
        $this->assertEquals('My second element', $this->collection->without('first')->first());
        $this->assertEquals(1, $this->collection->without('second')->not('third')->count());
        $this->assertEquals(0, $this->collection->without('first', 'second', 'third')->count());

        $this->assertIsUntouched();
    }

    public function testNth()
    {
        $this->assertEquals('My first element', $this->collection->nth(0));
        $this->assertEquals('My second element', $this->collection->nth(1));
        $this->assertEquals('My third element', $this->collection->nth(2));
        $this->assertNull($this->collection->nth(3));
    }

    public function testOffsetAndLimit()
    {
        $this->assertEquals(array_slice($this->sampleData, 1), $this->collection->offset(1)->toArray());
        $this->assertEquals(array_slice($this->sampleData, 0, 1), $this->collection->limit(1)->toArray());
        $this->assertEquals(array_slice($this->sampleData, 1, 1), $this->collection->offset(1)->limit(1)->toArray());
        $this->assertIsUntouched();
    }

    public function testPrepend()
    {
        // simple
        $collection = new Collection(['b', 'c']);
        $collection = $collection->prepend('a');

        $this->assertEquals([0, 1, 2], $collection->keys());
        $this->assertEquals(['a', 'b', 'c'], $collection->values());

        // with key
        $collection = new Collection(['b' => 'B', 'c' => 'C']);
        $collection = $collection->prepend('a', 'A');

        $this->assertEquals(['a', 'b', 'c'], $collection->keys());
        $this->assertEquals(['A', 'B', 'C'], $collection->values());

        // with too many params
        $collection = new Collection(['b' => 'B', 'c' => 'C']);
        $collection = $collection->prepend('a', 'A', 'ignore this');

        $this->assertEquals(['a', 'b', 'c'], $collection->keys());
        $this->assertEquals(['A', 'B', 'C'], $collection->values());
    }

    public function testQuery()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei',
            'four'  => 'vier'
        ]);

        $this->assertEquals([
            'two'   => 'zwei',
            'four'  => 'vier'
        ], $collection->query([
            'not'    => ['three'],
            'offset' => 1,
            'limit'  => 2
        ])->toArray());
    }

    public function testQueryPaginate()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei',
            'four'  => 'vier'
        ]);

        $this->assertEquals([
            'three' => 'drei',
            'four'  => 'vier'
        ], $collection->query([
            'paginate' => [
                'limit' => 2,
                'page'  => 2
            ]
        ])->toArray());
    }

    public function testQueryFilterBy()
    {
        $collection = new Collection([
            [
                'name'  => 'Bastian',
                'role'  => 'founder'
            ],
            [
                'name' => 'Nico',
                'role' => 'developer'
            ]
        ]);

        $this->assertEquals([
            [
                'name'  => 'Bastian',
                'role'  => 'founder'
            ]
        ], $collection->query([
            'filterBy' => [
                [
                    'field'    => 'name',
                    'operator' => '*=',
                    'value'    => 'Bast'
                ]
            ]
        ])->toArray());
    }

    public function testQuerySortBy()
    {
        $collection = new Collection([
            [
                'name'  => 'Bastian',
                'role'  => 'founder'
            ],
            [
                'name' => 'Nico',
                'role' => 'developer'
            ]
        ]);

        $this->assertEquals('Nico', $collection->query([
            'sortBy' => 'name desc'
        ])->first()['name']);
        $this->assertEquals('Bastian', $collection->query([
            'sortBy' => ['name', 'asc']
        ])->first()['name']);
    }

    public function testRemoveMultiple()
    {
        $collection = new Collection();

        $collection->set('a', 'A');
        $collection->set('b', 'B');
        $collection->set('c', 'C');

        $this->assertCount(3, $collection);

        foreach ($collection as $key => $item) {
            $collection->__unset($key);
        }

        $this->assertCount(0, $collection);
    }

    public function testSetters()
    {
        $this->collection->fourth = 'My fourth element';
        $this->collection->fifth  = 'My fifth element';

        $this->assertEquals('My fourth element', $this->collection->fourth);
        $this->assertEquals('My fifth element', $this->collection->fifth);

        $this->assertEquals('My fourth element', $this->collection->fourth());
        $this->assertEquals('My fifth element', $this->collection->fifth());

        $this->assertEquals('My fourth element', $this->collection->get('fourth'));
        $this->assertEquals('My fifth element', $this->collection->get('fifth'));
    }

    public function testShuffle()
    {
        $this->assertInstanceOf('Kirby\Toolkit\Collection', $this->collection->shuffle());
        $this->assertIsUntouched();
    }

    public function testSlice()
    {
        $this->assertEquals(array_slice($this->sampleData, 1), $this->collection->slice(1)->toArray());
        $this->assertEquals(2, $this->collection->slice(1)->count());
        $this->assertEquals(array_slice($this->sampleData, 0, 1), $this->collection->slice(0, 1)->toArray());
        $this->assertEquals(1, $this->collection->slice(0, 1)->count());
        $this->assertIsUntouched();
    }

    public function testToArray()
    {
        // associative
        $collection = new Collection($input = ['a' => 'value A', 'b' => 'value B']);
        $this->assertEquals($input, $collection->toArray());

        // non-associative
        $collection = new Collection($input = ['a', 'b', 'c']);
        $this->assertEquals($input, $collection->toArray());
    }

    public function testWhen()
    {
        $collection = new Collection([
            [
                'name'  => 'Bastian',
                'color' => 'blue'
            ],
            [
                'name' => 'Nico',
                'color' => 'green'
            ],
            [
                'name' => 'Lukas',
                'color' => 'yellow'
            ],
            [
                'name'  => 'Sonja',
                'color' => 'red'
            ]
        ]);

        $phpunit           = $this;
        $expectedCondition = null;

        $callback = function ($condition) use ($phpunit, &$expectedCondition) {
            $phpunit->assertSame($expectedCondition, $condition);
            return $this->sortBy('name', 'asc');
        };

        $fallback = function ($condition) use ($phpunit, &$expectedCondition) {
            $phpunit->assertSame($expectedCondition, $condition);
            return $this->sortBy('name', 'desc');
        };

        $sorted = $collection->when($expectedCondition = true, $callback);
        $this->assertSame('Bastian', $sorted->nth(0)['name']);
        $this->assertSame('Lukas', $sorted->nth(1)['name']);
        $this->assertSame('Nico', $sorted->nth(2)['name']);
        $this->assertSame('Sonja', $sorted->nth(3)['name']);

        $sorted = $collection->when($expectedCondition = 'this is truthy', $callback);
        $this->assertSame('Bastian', $sorted->nth(0)['name']);
        $this->assertSame('Lukas', $sorted->nth(1)['name']);
        $this->assertSame('Nico', $sorted->nth(2)['name']);
        $this->assertSame('Sonja', $sorted->nth(3)['name']);

        $sorted = $collection->when($expectedCondition = true, $callback, $fallback);
        $this->assertSame('Bastian', $sorted->nth(0)['name']);
        $this->assertSame('Lukas', $sorted->nth(1)['name']);
        $this->assertSame('Nico', $sorted->nth(2)['name']);
        $this->assertSame('Sonja', $sorted->nth(3)['name']);

        $sorted = $collection->when($expectedCondition = false, $callback);
        $this->assertSame('Bastian', $sorted->nth(0)['name']);
        $this->assertSame('Nico', $sorted->nth(1)['name']);
        $this->assertSame('Lukas', $sorted->nth(2)['name']);
        $this->assertSame('Sonja', $sorted->nth(3)['name']);

        $sorted = $collection->when($expectedCondition = false, $callback, $fallback);
        $this->assertSame('Sonja', $sorted->nth(0)['name']);
        $this->assertSame('Nico', $sorted->nth(1)['name']);
        $this->assertSame('Lukas', $sorted->nth(2)['name']);
        $this->assertSame('Bastian', $sorted->nth(3)['name']);

        $sorted = $collection->when($expectedCondition = null, $callback, $fallback);
        $this->assertSame('Sonja', $sorted->nth(0)['name']);
        $this->assertSame('Nico', $sorted->nth(1)['name']);
        $this->assertSame('Lukas', $sorted->nth(2)['name']);
        $this->assertSame('Bastian', $sorted->nth(3)['name']);
    }
}
