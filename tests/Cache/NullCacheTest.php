<?php

namespace Kirby\Cache;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Cache\NullCache
 */
class NullCacheTest extends TestCase
{
	/**
	 * @covers ::enabled
	 */
	public function testEnabled()
	{
		$cache = new NullCache();

		$this->assertFalse($cache->enabled());
	}

	/**
	 * @covers ::set
	 * @covers ::retrieve
	 * @covers ::remove
	 */
	public function testOperations()
	{
		$cache = new NullCache();

		$this->assertTrue($cache->set('foo', 'A basic value', 10));
		$this->assertFalse($cache->exists('foo'));
		$this->assertNull($cache->retrieve('foo'));
		$this->assertTrue($cache->remove('foo'));
	}

	/**
	 * @covers ::flush
	 */
	public function testFlush()
	{
		$cache = new NullCache();

		$this->assertTrue($cache->flush());
	}
}
