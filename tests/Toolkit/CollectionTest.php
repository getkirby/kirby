<?php

namespace Kirby\Toolkit;

class CollectionTest extends TestCase
{

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
                    'operator' => '^=',
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
