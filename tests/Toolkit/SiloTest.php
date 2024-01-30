<?php

namespace Kirby\Toolkit;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\Silo
 */
class SiloTest extends TestCase
{
	public function setUp(): void
	{
		Silo::$data = [];
	}

	/**
	 * @covers ::get
	 * @covers ::set
	 */
	public function testSetAndGet()
	{
		Silo::set('foo', 'bar');
		$this->assertSame('bar', Silo::get('foo'));
	}

	/**
	 * @covers ::set
	 */
	public function testSetArray()
	{
		Silo::set([
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame(['a' => 'A', 'b' => 'B'], Silo::get());
	}

	/**
	 * @covers ::get
	 */
	public function testGetArray()
	{
		Silo::set('a', 'A');
		Silo::set('b', 'B');

		$this->assertSame(['a' => 'A', 'b' => 'B'], Silo::get());
	}

	/**
	 * @covers ::remove
	 */
	public function testRemoveByKey()
	{
		Silo::set('a', 'A');
		$this->assertSame('A', Silo::get('a'));
		Silo::remove('a');
		$this->assertNull(Silo::get('a'));
	}

	/**
	 * @covers ::remove
	 */
	public function testRemoveAll()
	{
		Silo::set('a', 'A');
		Silo::set('b', 'B');
		$this->assertSame('A', Silo::get('a'));
		$this->assertSame('B', Silo::get('b'));
		Silo::remove();
		$this->assertNull(Silo::get('a'));
		$this->assertNull(Silo::get('b'));
	}
}
