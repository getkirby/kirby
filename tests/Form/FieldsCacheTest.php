<?php

namespace Kirby\Form;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldsCache::class)]
class FieldsCacheTest extends TestCase
{
	public function testOptions(): void
	{
		$cache = new FieldsCache();

		$this->assertSame(['a'], $cache->options('a', fn () => ['a']));
	}

	public function testOptionsWithDifferentKeys(): void
	{
		$cache = new FieldsCache();

		$this->assertSame(['a'], $cache->options('a', fn () => ['a']));
		$this->assertSame(['b'], $cache->options('b', fn () => ['b']));
	}

	public function testOptionsWithEmptyResult(): void
	{
		$cache = new FieldsCache();

		// an empty result is a valid result and must be kept
		$this->assertSame([], $cache->options('a', fn () => []));
		$this->assertSame([], $cache->options('a', fn () => ['later']));
	}

	public function testOptionsWithSameKey(): void
	{
		$cache = new FieldsCache();

		$this->assertSame(['a'], $cache->options('a', fn () => ['a']));

		// the stored result is returned, the second closure is never used
		$this->assertSame(['a'], $cache->options('a', fn () => ['b']));
	}
}
