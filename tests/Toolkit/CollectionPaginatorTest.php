<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass \Kirby\Toolkit\Collection
 */
class CollectionPaginatorTest extends TestCase
{
	/**
	 * @covers ::slice
	 */
	public function testSlice()
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

	/**
	 * @covers ::slice
	 */
	public function testSliceNotReally()
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

	/**
	 * @covers ::limit
	 */
	public function testLimit()
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

	/**
	 * @covers ::offset
	 */
	public function testOffset()
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

	/**
	 * @covers ::paginate
	 * @covers ::pagination
	 */
	public function testPaginate()
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

	/**
	 * @covers ::chunk
	 */
	public function testChunk()
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
