<?php

namespace Kirby\Toolkit;

class CollectionNavigatorTest extends TestCase
{
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
