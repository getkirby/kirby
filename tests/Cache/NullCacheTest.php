<?php

namespace Kirby\Cache;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NullCache::class)]
class NullCacheTest extends TestCase
{
	public function testEnabled(): void
	{
		$cache = new NullCache();

		$this->assertFalse($cache->enabled());
	}

	public function testOperations(): void
	{
		$cache = new NullCache();

		$this->assertTrue($cache->set('foo', 'A basic value', 10));
		$this->assertFalse($cache->exists('foo'));
		$this->assertNull($cache->retrieve('foo'));
		$this->assertTrue($cache->remove('foo'));
	}

	public function testFlush(): void
	{
		$cache = new NullCache();

		$this->assertTrue($cache->flush());
	}
}
