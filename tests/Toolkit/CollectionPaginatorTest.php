<?php

namespace Kirby\Toolkit;

class CollectionPaginatorTest extends TestCase
{
    public function testSlice()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei',
            'four'  => 'vier',
            'five'  => 'fünf'
        ]);

        $this->assertEquals('drei', $collection->slice(2)->first());
        $this->assertEquals('vier', $collection->slice(2, 2)->last());
    }

    public function testSliceNotReally()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei',
            'four'  => 'vier',
            'five'  => 'fünf'
        ]);

        $this->assertEquals($collection, $collection->slice());
    }

    public function testLimit()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei',
            'four'  => 'vier',
            'five'  => 'fünf'
        ]);

        $this->assertEquals('drei', $collection->limit(3)->last());
        $this->assertEquals('fünf', $collection->limit(99)->last());
    }

    public function testOffset()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei',
            'four'  => 'vier',
            'five'  => 'fünf'
        ]);

        $this->assertEquals('drei', $collection->offset(2)->first());
        $this->assertEquals('vier', $collection->offset(3)->first());
        $this->assertEquals(null, $collection->offset(99)->first());
    }

    public function testPaginate()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei',
            'four'  => 'vier',
            'five'  => 'fünf'
        ]);

        $this->assertEquals('eins', $collection->paginate(2)->first());
        $this->assertEquals('drei', $collection->paginate(2, 2)->first());

        $this->assertEquals('eins', $collection->paginate([
            'foo' => 'bar'
        ])->first());
        $this->assertEquals('fünf', $collection->paginate([
            'limit' => 2,
            'page' => 3
        ])->first());

        $this->assertEquals(3, $collection->pagination()->page());
    }

    public function testChunk()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei',
            'four'  => 'vier',
            'five'  => 'fünf'
        ]);

        $this->assertEquals(3, $collection->chunk(2)->count());
        $this->assertEquals('eins', $collection->chunk(2)->first()->first());
        $this->assertEquals('fünf', $collection->chunk(2)->last()->first());
    }
}
