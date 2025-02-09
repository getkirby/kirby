<?php

namespace Kirby\Cache;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionMethod;

#[CoversClass(Cache::class)]
class CacheTest extends TestCase
{
	public function testConstruct()
	{
		$cache = new TestCache(['some' => 'options']);
		$this->assertSame(['some' => 'options'], $cache->options());
	}

	public function testKey()
	{
		$method = new ReflectionMethod(Cache::class, 'key');
		$method->setAccessible(true);

		$cache = new TestCache();
		$this->assertSame('foo', $method->invoke($cache, 'foo'));

		$cache = new TestCache([
			'prefix' => 'test'
		]);
		$this->assertSame('test/foo', $method->invoke($cache, 'foo'));
	}

	public function testGet()
	{
		$cache = new TestCache();

		$cache->set('foo', 'foo');
		$this->assertSame('foo', $cache->get('foo'));

		$cache->set('foo', ['this is' => 'an array']);
		$this->assertSame(['this is' => 'an array'], $cache->get('foo'));

		$cache->set('foo', 1234);
		$this->assertSame(1234, $cache->get('foo'));

		$cache->set('foo', null);
		$this->assertNull($cache->get('foo', 'default'));

		$this->assertSame('default', $cache->get('doesnotexist', 'default'));

		$cache->set('notyetexpired', 'foo', 10, time());
		$this->assertSame('foo', $cache->get('notyetexpired'));

		$cache->set('expired', 'foo', 10, 0);
		$this->assertTrue(isset($cache->store['expired']));
		$this->assertSame('default', $cache->get('expired', 'default'));
		$this->assertFalse(isset($cache->store['expired']));
	}

	public function testGetOrSet()
	{
		$cache = new TestCache();
		$count = 0;

		$callback = function () use (&$count) {
			$count++;
			return 'foo';
		};

		$this->assertSame(0, $count);
		$this->assertSame('foo', $cache->getOrSet('bar', $callback));
		$this->assertSame(1, $count);
		$this->assertSame('foo', $cache->getOrSet('bar', $callback));
		$this->assertSame(1, $count);
	}

	public function testEnabled()
	{
		$cache = new TestCache();

		$this->assertTrue($cache->enabled());
	}

	public function testExpiration()
	{
		$method = new ReflectionMethod(Cache::class, 'expiration');
		$method->setAccessible(true);

		$cache = new TestCache();
		$this->assertSame(0, $method->invoke($cache));
		$this->assertSame(0, $method->invoke($cache, 0));
		$this->assertSame(time() + 600, $method->invoke($cache, 10));
	}

	public function testExpires()
	{
		$cache = new TestCache();

		$cache->set('foo', 'foo', 12);
		$this->assertSame(time() + 720, $cache->expires('foo'));

		$cache->set('foo', 'foo');
		$this->assertNull($cache->expires('foo'));

		$this->assertFalse($cache->expires('doesnotexist'));
	}

	public function testExpired()
	{
		$cache = new TestCache();

		$cache->set('foo', 'foo');
		$this->assertFalse($cache->expired('foo'));

		$cache->set('foo', 'foo', 10, 0);
		$this->assertTrue($cache->expired('foo'));

		$cache->set('foo', 'foo', 10);
		$this->assertFalse($cache->expired('foo'));

		$this->assertTrue($cache->expired('doesnotexist'));
	}

	public function testCreated()
	{
		$cache = new TestCache();

		$cache->set('foo', 'foo', 10, 1234);
		$this->assertSame(1234, $cache->created('foo'));
		$this->assertSame(1234, $cache->modified('foo'));

		$this->assertFalse($cache->created('doesnotexist'));
		$this->assertFalse($cache->modified('doesnotexist'));
	}

	public function testExists()
	{
		$cache = new TestCache();

		$cache->set('foo', 'foo');
		$this->assertTrue($cache->exists('foo'));

		$cache->set('foo', 'foo', 10, 0);
		$this->assertFalse($cache->exists('foo'));

		$this->assertFalse($cache->exists('doesnotexist'));
	}
}
