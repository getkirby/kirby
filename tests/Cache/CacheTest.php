<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TestCache extends Cache
{
    public $store = [];

    public function set(string $key, $value, int $minutes = 0, int $created = null): bool
    {
        $value = new Value($value, $minutes, $created);
        $this->store[$key] = $value;
        return true;
    }

    public function retrieve(string $key): ?Value
    {
        return $this->store[$key] ?? null;
    }

    public function remove(string $key): bool
    {
        unset($this->store[$key]);
        return true;
    }

    public function flush(): bool
    {
        $this->store = [];
        return true;
    }
}

/**
 * @coversDefaultClass \Kirby\Cache\NullCache
 */
class CacheTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::options
     */
    public function testConstruct()
    {
        $cache = new TestCache(['some' => 'options']);
        $this->assertEquals(['some' => 'options'], $cache->options());
    }

    /**
     * @covers ::key
     */
    public function testKey()
    {
        $method = new ReflectionMethod(Cache::class, 'key');
        $method->setAccessible(true);

        $cache = new TestCache();
        $this->assertEquals('foo', $method->invoke($cache, 'foo'));

        $cache = new TestCache([
            'prefix' => 'test'
        ]);
        $this->assertEquals('test/foo', $method->invoke($cache, 'foo'));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $cache = new TestCache();

        $cache->set('foo', 'foo');
        $this->assertEquals('foo', $cache->get('foo'));

        $cache->set('foo', ['this is' => 'an array']);
        $this->assertEquals(['this is' => 'an array'], $cache->get('foo'));

        $cache->set('foo', 1234);
        $this->assertEquals(1234, $cache->get('foo'));

        $cache->set('foo', null);
        $this->assertEquals(null, $cache->get('foo', 'default'));

        $this->assertEquals('default', $cache->get('doesnotexist', 'default'));

        $cache->set('notyetexpired', 'foo', 60, time());
        $this->assertEquals('foo', $cache->get('notyetexpired'));

        $cache->set('expired', 'foo', 60, 0);
        $this->assertTrue(isset($cache->store['expired']));
        $this->assertEquals('default', $cache->get('expired', 'default'));
        $this->assertFalse(isset($cache->store['expired']));
    }

    /**
     * @covers ::expiration
     */
    public function testExpiration()
    {
        $method = new ReflectionMethod(Cache::class, 'expiration');
        $method->setAccessible(true);

        $cache = new TestCache();
        $this->assertEquals(0, $method->invoke($cache));
        $this->assertEquals(0, $method->invoke($cache, 0));
        $this->assertEquals(time() + 600, $method->invoke($cache, 10));
    }

    /**
     * @covers ::expires
     */
    public function testExpires()
    {
        $cache = new TestCache();

        $cache->set('foo', 'foo', 12);
        $this->assertEquals(time() + 720, $cache->expires('foo'));

        $cache->set('foo', 'foo');
        $this->assertEquals(null, $cache->expires('foo'));

        $this->assertFalse($cache->expires('doesnotexist'));
    }

    /**
     * @covers ::expired
     */
    public function testExpired()
    {
        $cache = new TestCache();

        $cache->set('foo', 'foo');
        $this->assertFalse($cache->expired('foo'));

        $cache->set('foo', 'foo', 60, 0);
        $this->assertTrue($cache->expired('foo'));

        $cache->set('foo', 'foo', 60);
        $this->assertFalse($cache->expired('foo'));

        $this->assertTrue($cache->expired('doesnotexist'));
    }

    /**
     * @covers ::created
     * @covers ::modified
     */
    public function testCreated()
    {
        $cache = new TestCache();

        $cache->set('foo', 'foo', 60, 1234);
        $this->assertEquals(1234, $cache->created('foo'));
        $this->assertEquals(1234, $cache->modified('foo'));

        $this->assertFalse($cache->created('doesnotexist'));
        $this->assertFalse($cache->modified('doesnotexist'));
    }

    /**
     * @covers ::exists
     */
    public function testExists()
    {
        $cache = new TestCache();

        $cache->set('foo', 'foo');
        $this->assertTrue($cache->exists('foo'));

        $cache->set('foo', 'foo', 60, 0);
        $this->assertFalse($cache->exists('foo'));

        $this->assertFalse($cache->exists('doesnotexist'));
    }
}
