<?php

namespace Kirby\Cache;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Redis;
use ReflectionProperty;
use Throwable;

#[CoversClass(RedisCache::class)]
class RedisCacheTest extends TestCase
{
	protected function setUp(): void
	{
		if (class_exists('Redis') === false) {
			$this->markTestSkipped('The Redis extension is not available.');
			return;
		}

		try {
			$connection = new Redis();
			$connection->ping();
		} catch (Throwable) {
			$this->markTestSkipped('The Redis server is not running.');
		}
	}

	protected function tearDown(): void
	{
		$connection = new RedisCache();
		$connection->flush();
	}

	public function testConstructServer(): void
	{
		// invalid port
		$cache = new RedisCache([
			'port' => 1234
		]);
		$this->assertFalse($cache->enabled());

		// invalid host
		$cache = new RedisCache([
			'host' => 'invalid.host'
		]);
		$this->assertFalse($cache->enabled());
	}

	public function testDatabase(): void
	{
		$cache = new RedisCache([
			'database' => 1
		]);

		$cache->set('a', 'A basic value');

		$this->assertSame(1, $cache->databaseNum());
		$this->assertTrue($cache->exists('a'));
		$this->assertSame('A basic value', $cache->retrieve('a')->value());
	}

	public function testEnabled(): void
	{
		$cache = new RedisCache();
		$this->assertTrue($cache->enabled());
	}

	public function testFlush(): void
	{
		$cache = new RedisCache();

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

	public function testFlushWithPrefix(): void
	{
		$cache1 = new RedisCache([
			'prefix' => 'test1:'
		]);
		$cache2 = new RedisCache([
			'prefix' => 'test2:'
		]);

		$cache1->set('a', 'A basic value');
		$cache1->set('b', 'A basic value');
		$cache2->set('a', 'A basic value');
		$cache2->set('b', 'A basic value');
		$cache1->set('c/a', 'A basic value');
		$cache2->set('c/a', 'A basic value');

		$this->assertTrue($cache1->exists('a'));
		$this->assertTrue($cache1->exists('b'));
		$this->assertTrue($cache1->exists('c/a'));
		$this->assertTrue($cache2->exists('a'));
		$this->assertTrue($cache2->exists('b'));
		$this->assertTrue($cache2->exists('c/a'));

		$this->assertTrue($cache1->flush());

		$this->assertFalse($cache1->exists('a'));
		$this->assertFalse($cache1->exists('b'));
		$this->assertFalse($cache1->exists('c/a'));
		$this->assertTrue($cache2->exists('a'));
		$this->assertTrue($cache2->exists('b'));
		$this->assertTrue($cache2->exists('c/a'));
	}

	public function testFlushWithGlobPrefix(): void
	{
		$cache1 = new RedisCache([
			'prefix' => 'test[1]:'
		]);
		$cache2 = new RedisCache([
			'prefix' => 'test1:'
		]);

		$cache1->set('a', 'A basic value');
		$cache2->set('a', 'A basic value');

		// without escaping, the pattern test[1]:* is read as a
		// character class and would also match test1: keys
		$this->assertTrue($cache1->flush());

		$this->assertFalse($cache1->exists('a'));
		$this->assertTrue($cache2->exists('a'));
	}

	public function testFlushRestoresScanOption(): void
	{
		$cache = new RedisCache([
			'prefix' => 'test1:'
		]);

		$cache->set('a', 'A basic value');

		$connection = (new ReflectionProperty(RedisCache::class, 'connection'))->getValue($cache);

		// default state before flushing
		$this->assertSame(Redis::SCAN_NORETRY, $connection->getOption(Redis::OPT_SCAN));

		$cache->flush();

		// flush must not leak its temporary SCAN_RETRY flag
		$this->assertSame(Redis::SCAN_NORETRY, $connection->getOption(Redis::OPT_SCAN));
	}

	public function testFlushRestoresCombinedScanOption(): void
	{
		$cache = new RedisCache([
			'prefix' => 'test1:'
		]);

		$cache->set('a', 'A basic value');

		$connection = (new ReflectionProperty(RedisCache::class, 'connection'))->getValue($cache);

		// enable both the retry and prefix flags (combined bitmask = 3)
		$connection->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
		$connection->setOption(Redis::OPT_SCAN, Redis::SCAN_PREFIX);
		$this->assertSame(
			Redis::SCAN_RETRY | Redis::SCAN_PREFIX,
			$connection->getOption(Redis::OPT_SCAN)
		);

		$cache->flush();

		// both flags must be restored,
		// not collapsed by a naive setOption(3)
		$this->assertSame(
			Redis::SCAN_RETRY | Redis::SCAN_PREFIX,
			$connection->getOption(Redis::OPT_SCAN)
		);
	}

	public function testOperations(): void
	{
		$cache = new RedisCache();

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

	public function testOperationsWithPrefix(): void
	{
		$cache1 = new RedisCache([
			'prefix' => 'test1:'
		]);
		$cache2 = new RedisCache([
			'prefix' => 'test2:'
		]);

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
}
