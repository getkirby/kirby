<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass \Kirby\Toolkit\Iterator
 */
class IteratorTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$iterator = new Iterator($expected = [
			'one' => 'eins',
			'two' => 'zwei',
		]);

		$this->assertSame($expected, $iterator->data);
	}

	/**
	 * @covers ::key
	 */
	public function testKey()
	{
		$iterator = new Iterator([
			'one' => 'eins',
			'two' => 'zwei',
		]);

		$this->assertSame('one', $iterator->key());
	}

	/**
	 * @covers ::keys
	 */
	public function testKeys()
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$this->assertSame([
			'one',
			'two',
			'three'
		], $iterator->keys());
	}

	/**
	 * @covers ::current
	 */
	public function testCurrent()
	{
		$iterator = new Iterator([
			'one' => 'eins',
			'two' => 'zwei',
		]);

		$this->assertSame('eins', $iterator->current());
	}

	/**
	 * @covers ::current
	 * @covers ::next
	 * @covers ::prev
	 */
	public function testPrevNext()
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$this->assertSame('eins', $iterator->current());

		$iterator->next();
		$this->assertSame('zwei', $iterator->current());

		$iterator->next();
		$this->assertSame('drei', $iterator->current());

		$iterator->prev();
		$this->assertSame('zwei', $iterator->current());

		$iterator->prev();
		$this->assertSame('eins', $iterator->current());
	}

	/**
	 * @covers ::rewind
	 */
	public function testRewind()
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$iterator->next();
		$iterator->next();
		$this->assertSame('drei', $iterator->current());

		$iterator->rewind();
		$this->assertSame('eins', $iterator->current());
	}

	/**
	 * @covers ::valid
	 */
	public function testValid()
	{
		$iterator = new Iterator([]);
		$this->assertFalse($iterator->valid());

		$iterator = new Iterator(['one' => 'eins']);
		$this->assertTrue($iterator->valid());
	}

	/**
	 * @covers ::count
	 */
	public function testCount()
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);
		$this->assertSame(3, $iterator->count());

		$iterator = new Iterator(['one' => 'eins']);
		$this->assertSame(1, $iterator->count());

		$iterator = new Iterator([]);
		$this->assertSame(0, $iterator->count());
	}

	/**
	 * @covers ::indexOf
	 */
	public function testIndexOf()
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$this->assertSame(0, $iterator->indexOf('eins'));
		$this->assertSame(1, $iterator->indexOf('zwei'));
		$this->assertSame(2, $iterator->indexOf('drei'));
	}

	/**
	 * @covers ::keyOf
	 */
	public function testKeyOf()
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$this->assertSame('one', $iterator->keyOf('eins'));
		$this->assertSame('two', $iterator->keyOf('zwei'));
		$this->assertSame('three', $iterator->keyOf('drei'));
	}

	/**
	 * @covers ::has
	 */
	public function testHas()
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei'
		]);

		$this->assertTrue($iterator->has('one'));
		$this->assertTrue($iterator->has('two'));
		$this->assertFalse($iterator->has('three'));
	}

	/**
	 * @covers ::__isset
	 */
	public function testIsset()
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei'
		]);

		$this->assertTrue(isset($iterator->one));
		$this->assertTrue(isset($iterator->two));
		$this->assertFalse(isset($iterator->three));
	}

	/**
	 * @covers ::__debugInfo
	 */
	public function testDebugInfo()
	{
		$array = [
			'one'   => 'eins',
			'two'   => 'zwei'
		];

		$iterator = new Iterator($array);
		$this->assertSame($array, $iterator->__debugInfo());
	}
}
