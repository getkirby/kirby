<?php

namespace Kirby\Toolkit;

class CollectionGetterTest extends TestCase
{
	public function testGetMagic(): void
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame('eins', $collection->one);
		$this->assertSame('eins', $collection->ONE);
		$this->assertNull($collection->three);
	}

	public function testGet(): void
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame('eins', $collection->get('one'));
		$this->assertNull($collection->get('three'));
		$this->assertSame('default', $collection->get('three', 'default'));
	}

	public function testMagicMethods(): void
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame('eins', $collection->one());
		$this->assertSame('zwei', $collection->two());
		$this->assertNull($collection->three());
	}

	public function testGetAttribute(): void
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame('eins', $collection->getAttribute($collection->toArray(), 'one'));
		$this->assertNull($collection->getAttribute($collection->toArray(), 'three'));

		$this->assertSame('zwei', $collection->getAttribute($collection, 'two'));
		$this->assertNull($collection->getAttribute($collection, 'three'));
	}
}
