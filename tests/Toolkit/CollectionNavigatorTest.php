<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass \Kirby\Toolkit\Collection
 */
class CollectionNavigatorTest extends TestCase
{
	/**
	 * @covers ::first
	 * @covers ::last
	 */
	public function testFirstLast()
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei',
			'four'  => 'vier'
		]);

		$this->assertSame('eins', $collection->first());
		$this->assertSame('vier', $collection->last());
	}

	/**
	 * @covers ::nth
	 */
	public function testNth()
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei',
			'four'  => 'vier'
		]);

		$this->assertSame('eins', $collection->nth(0));
		$this->assertSame('zwei', $collection->nth(1));
		$this->assertSame('drei', $collection->nth(2));
		$this->assertSame('vier', $collection->nth(3));
	}
}
