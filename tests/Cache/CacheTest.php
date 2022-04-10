<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;

require_once __DIR__ . '/mocks.php';

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
        $this->assertSame(['some' => 'options'], $cache->options());
    }

    /**
     * @covers ::key
     */
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

    /**
     * @covers ::get
     * @covers ::getObject
     * @covers ::set
     */
    public function testGetSet()
    {
        $cache = new TestCache();

        $cache->set('foo', 'foo');
        $this->assertSame('foo', $cache->get('foo'));
        $object = $cache->getObject('foo');
        $this->assertSame('foo', $object->value());
        $this->assertSame(time(), $object->created());
        $this->assertNull($object->expires());

        $cache->set('foo', ['this is' => 'an array']);
        $this->assertSame(['this is' => 'an array'], $cache->get('foo'));

        $cache->set('foo', 1234);
        $this->assertSame(1234, $cache->get('foo'));

        $cache->set('foo', null);
        $this->assertNull($cache->get('foo', 'default'));
        $object = $cache->getObject('foo');
        $this->assertNull($object->value());
        $this->assertSame(time(), $object->created());
        $this->assertNull($object->expires());

        $this->assertSame('default', $cache->get('doesnotexist', 'default'));
        $this->assertNull($cache->getObject('doesnotexist'));

        $cache->set('notyetexpired', 'foo', 10);
        $this->assertSame('foo', $cache->get('notyetexpired'));
        $object = $cache->getObject('notyetexpired');
        $this->assertSame('foo', $object->value());
        $this->assertSame(time(), $object->created());
        $this->assertSame(time() + 600, $object->expires());

        $cache->setWithCreated('expired', 'foo', 10, 0);
        $this->assertTrue(isset($cache->store['expired']));
        $this->assertSame('default', $cache->get('expired', 'default'));
        $this->assertNull($cache->getObject('expired'));
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
        $this->assertSame(0, $method->invoke($cache));
        $this->assertSame(0, $method->invoke($cache, 0));
        $this->assertSame(time() + 600, $method->invoke($cache, 10));
    }

    /**
     * @covers ::expires
     */
    public function testExpires()
    {
        $cache = new TestCache();

        $cache->set('foo', 'foo', 12);
        $this->assertSame(time() + 720, $cache->expires('foo'));

        $cache->set('foo', 'foo');
        $this->assertNull($cache->expires('foo'));

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

        $cache->setWithCreated('foo', 'foo', 10, 0);
        $this->assertTrue($cache->expired('foo'));

        $cache->set('foo', 'foo', 10);
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

        $cache->setWithCreated('foo', 'foo', 10, 1234);
        $this->assertSame(1234, $cache->created('foo'));
        $this->assertSame(1234, $cache->modified('foo'));

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

        $cache->setWithCreated('foo', 'foo', 10, 0);
        $this->assertFalse($cache->exists('foo'));

        $this->assertFalse($cache->exists('doesnotexist'));
    }
}
