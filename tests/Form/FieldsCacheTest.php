<?php

namespace Kirby\Form;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldsCache::class)]
class FieldsCacheTest extends TestCase
{
	public function testGetOrSet(): void
	{
		$cache = new FieldsCache();

		$this->assertSame(['a'], $cache->getOrSet('a', fn () => ['a']));
	}

	public function testGetOrSetWithDifferentKeys(): void
	{
		$cache = new FieldsCache();

		$this->assertSame(['a'], $cache->getOrSet('a', fn () => ['a']));
		$this->assertSame(['b'], $cache->getOrSet('b', fn () => ['b']));
	}

	public function testGetOrSetWithEmptyResult(): void
	{
		$cache = new FieldsCache();

		// an empty result is a valid result and must be kept
		$this->assertSame([], $cache->getOrSet('a', fn () => []));
		$this->assertSame([], $cache->getOrSet('a', fn () => ['later']));
	}

	public function testGetOrSetWithNullResult(): void
	{
		$cache = new FieldsCache();

		// unlike `Kirby\Cache\Cache`, null is a valid result
		$this->assertNull($cache->getOrSet('a', fn () => null));
		$this->assertNull($cache->getOrSet('a', fn () => 'later'));
	}

	public function testGetOrSetWithSameKey(): void
	{
		$cache = new FieldsCache();

		$this->assertSame(['a'], $cache->getOrSet('a', fn () => ['a']));

		// the stored result is returned, the second closure is never used
		$this->assertSame(['a'], $cache->getOrSet('a', fn () => ['b']));
	}
}
