<?php

namespace Kirby\Toolkit;

class CollectionPaginatorTest extends TestCase
{
	public function testSlice(): void
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei',
			'four'  => 'vier',
			'five'  => 'fünf'
		]);

		$this->assertSame('drei', $collection->slice(2)->first());
		$this->assertSame('vier', $collection->slice(2, 2)->last());
	}

	public function testSliceNotReally(): void
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei',
			'four'  => 'vier',
			'five'  => 'fünf'
		]);

		$this->assertSame($collection, $collection->slice());
	}

	public function testLimit(): void
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei',
			'four'  => 'vier',
			'five'  => 'fünf'
		]);

		$this->assertSame('drei', $collection->limit(3)->last());
		$this->assertSame('fünf', $collection->limit(99)->last());
	}

	public function testOffset(): void
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei',
			'four'  => 'vier',
			'five'  => 'fünf'
		]);

		$this->assertSame('drei', $collection->offset(2)->first());
		$this->assertSame('vier', $collection->offset(3)->first());
		$this->assertNull($collection->offset(99)->first());
	}

	public function testPaginate(): void
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei',
			'four'  => 'vier',
			'five'  => 'fünf'
		]);

		$this->assertSame('eins', $collection->paginate(2)->first());
		$this->assertSame('drei', $collection->paginate(2, 2)->first());

		$this->assertSame('eins', $collection->paginate([
			'foo' => 'bar'
		])->first());
		$this->assertSame('fünf', $collection->paginate([
			'limit' => 2,
			'page' => 3
		])->first());

		$this->assertSame(3, $collection->pagination()->page());
	}

	public function testChunk(): void
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei',
			'four'  => 'vier',
			'five'  => 'fünf'
		]);

		$this->assertCount(3, $collection->chunk(2));
		$this->assertSame('eins', $collection->chunk(2)->first()->first());
		$this->assertSame('fünf', $collection->chunk(2)->last()->first());
	}
}
