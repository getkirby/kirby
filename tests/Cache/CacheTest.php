<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TestCache extends Cache
{
    protected $store = [];

    public function set(string $key, $value, int $minutes = 0, int $created = null)
    {
        $value = new Value($value, $minutes, $created);
        $this->store[$key] = $value;
    }
    public function retrieve(string $key)
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

class CacheTest extends TestCase
{
    public function testConstruct()
    {
        $driver = new TestCache();
        $this->assertInstanceOf(TestCache::class, $driver);
    }

    public function testGet()
    {
        $driver = new TestCache();
        $driver->set('foo', 'foo');
        $this->assertEquals('foo', $driver->get('foo'));
    }

    public function testGetDefault()
    {
        $driver = new TestCache();
        $this->assertEquals('default', $driver->get('foo', 'default'));
    }

    public function testGetExpired()
    {
        $driver = new TestCache();
        $driver->set('foo', 'foo', 60, 0);
        $this->assertEquals('none', $driver->get('foo', 'none'));
        $this->assertFalse($driver->exists('foo'));
    }

    public function testExpiration()
    {
        $driver = new TestCache();
        $ref = new ReflectionClass($driver);
        $expiration = $ref->getMethod('expiration');
        $expiration->setAccessible(true);

        $this->assertEquals(time() + (60 * 2628000), $expiration->invoke($driver));
        $this->assertEquals(time() + 600, $expiration->invoke($driver, 10));
    }

    public function testExpires()
    {
        $driver = new TestCache();
        $driver->set('foo', 'foo', 12);
        $this->assertEquals(time() + 720, $driver->expires('foo'));
    }

    public function testExpiresNonValue()
    {
        $driver = new TestCache();
        $this->assertfalse($driver->expires('missing'));
    }

    public function testExpired()
    {
        $driver = new TestCache();
        $driver->set('foo', 'foo');
        $this->assertFalse($driver->expired('foo'));
    }

    public function testCreated()
    {
        $driver = new TestCache();
        $driver->set('foo', 'foo');
        $this->assertEquals(time(), $driver->created('foo'));
        $this->assertEquals(time(), $driver->modified('foo'));
    }

    public function testCreatedNonValue()
    {
        $driver = new TestCache();
        $this->assertFalse($driver->created('missing'));
        $this->assertFalse($driver->modified('missing'));
    }

    public function testExists()
    {
        $driver = new TestCache();
        $driver->set('foo', 'foo');
        $this->assertTrue($driver->exists('foo'));
        $this->assertFalse($driver->exists('missing'));
    }
}
