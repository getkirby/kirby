<?php

namespace Kirby\Toolkit;

class CollectionTest extends TestCase
{
    public function setUp(): void
    {
        $this->data = [
            'first'  => 'My first element',
            'second' => 'My second element',
            'third'  => 'My third element',
        ];

        $this->collection = new Collection($this->data);
    }

    public function assertIsUntouched()
    {
        // the original collection must to be untouched
        $this->assertEquals($this->data, $this->collection->toArray());
    }

    public function testAppend()
    {
        // simple
        $collection = new Collection;
        $collection = $collection->append('a');
        $collection = $collection->append('b');
        $collection = $collection->append('c');

        $this->assertEquals([0, 1, 2], $collection->keys());

        // with key
        $collection = new Collection;
        $collection = $collection->append('a', 'A');
        $collection = $collection->append('b', 'B');
        $collection = $collection->append('c', 'C');

        $this->assertEquals(['a', 'b', 'c'], $collection->keys());
    }

    public function testCount()
    {
        $this->assertEquals(3, $this->collection->count());
    }

    public function testFilter()
    {
        $func = function ($element) {
            return ($element == "My second element") ? true : false;
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
        $this->assertEquals(array_reverse($this->data, true), $this->collection->flip()->toArray());
        $this->assertEquals($this->data, $this->collection->flip()->flip()->toArray());
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

    public function testIndexOf()
    {
        $this->assertEquals(1, $this->collection->indexOf('My second element'));
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

    public function testIsNotEmpty()
    {
        $collection = new Collection([]);

        $this->assertTrue($collection->isEmpty());
        $this->assertFalse($collection->isNotEmpty());
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
        $this->assertEquals(array_slice($this->data, 1), $this->collection->offset(1)->toArray());
        $this->assertEquals(array_slice($this->data, 0, 1), $this->collection->limit(1)->toArray());
        $this->assertEquals(array_slice($this->data, 1, 1), $this->collection->offset(1)->limit(1)->toArray());
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
        $this->assertEquals(array_slice($this->data, 1), $this->collection->slice(1)->toArray());
        $this->assertEquals(2, $this->collection->slice(1)->count());
        $this->assertEquals(array_slice($this->data, 0, 1), $this->collection->slice(0, 1)->toArray());
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
}
