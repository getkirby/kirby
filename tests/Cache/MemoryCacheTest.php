<?php

namespace Kirby\Cache;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MemoryCache::class)]
class MemoryCacheTest extends TestCase
{
	public function testEnabled(): void
	{
		$cache = new MemoryCache();

		$this->assertTrue($cache->enabled());
	}

	public function testOperations(): void
	{
		$cache = new MemoryCache();

		$time = time();
		$this->assertTrue($cache->set('foo', 'A basic value', 10));

		$this->assertTrue($cache->exists('foo'));
		$this->assertSame('A basic value', $cache->retrieve('foo')->value());
		$this->assertSame($time, $cache->created('foo'));
		$this->assertSame($time + 600, $cache->expires('foo'));

		$this->assertTrue($cache->remove('foo'));
		$this->assertFalse($cache->exists('foo'));
		$this->assertNull($cache->retrieve('foo'));

		$this->assertFalse($cache->remove('doesnotexist'));
	}

	public function testOperationsWithMultipleInstances(): void
	{
		$cache1 = new MemoryCache();
		$cache2 = new MemoryCache();

		$time = time();
		$this->assertTrue($cache1->set('foo', 'A basic value', 10));

		$this->assertTrue($cache1->exists('foo'));
		$this->assertFalse($cache2->exists('foo'));
		$this->assertSame('A basic value', $cache1->retrieve('foo')->value());
		$this->assertSame($time, $cache1->created('foo'));
		$this->assertSame($time + 600, $cache1->expires('foo'));

		$this->assertTrue($cache2->set('foo', 'Another basic value'));
		$this->assertTrue($cache2->exists('foo'));

		$this->assertSame('A basic value', $cache1->retrieve('foo')->value());
		$this->assertTrue($cache1->remove('foo'));
		$this->assertFalse($cache1->exists('foo'));
		$this->assertNull($cache1->retrieve('foo'));
		$this->assertTrue($cache2->exists('foo'));
		$this->assertSame('Another basic value', $cache2->retrieve('foo')->value());
	}

	public function testFlush(): void
	{
		$cache = new MemoryCache();

		$cache->set('a', 'A basic value');
		$cache->set('b', 'A basic value');
		$cache->set('c', 'A basic value');
		$this->assertTrue($cache->exists('a'));
		$this->assertTrue($cache->exists('b'));
		$this->assertTrue($cache->exists('c'));

		$this->assertTrue($cache->flush());
		$this->assertFalse($cache->exists('a'));
		$this->assertFalse($cache->exists('b'));
		$this->assertFalse($cache->exists('c'));
	}

	public function testFlushWithMultipleInstances(): void
	{
		$cache1 = new MemoryCache();
		$cache2 = new MemoryCache();

		$cache1->set('a', 'A basic value');
		$cache1->set('b', 'A basic value');
		$cache2->set('a', 'A basic value');
		$cache2->set('b', 'A basic value');
		$this->assertTrue($cache1->exists('a'));
		$this->assertTrue($cache1->exists('b'));
		$this->assertTrue($cache2->exists('a'));
		$this->assertTrue($cache2->exists('b'));

		$this->assertTrue($cache1->flush());
		$this->assertFalse($cache1->exists('a'));
		$this->assertFalse($cache1->exists('b'));
		$this->assertTrue($cache2->exists('a'));
		$this->assertTrue($cache2->exists('b'));
	}

	public function testModified(): void
	{
		$cache = new MemoryCache();

		$time = time();

		$cache->set('a', 'A basic value');

		$this->assertGreaterThanOrEqual($time, $cache->modified('a'));
	}
}
