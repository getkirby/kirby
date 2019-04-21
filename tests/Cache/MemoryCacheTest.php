<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Kirby\Toolkit\Dir;

/**
 * @coversDefaultClass \Kirby\Cache\MemoryCache
 */
class MemoryCacheTest extends TestCase
{
    /**
     * @covers ::set
     * @covers ::retrieve
     * @covers ::remove
     */
    public function testOperations()
    {
        $cache = new MemoryCache();

        $time = time();
        $this->assertTrue($cache->set('foo', 'A basic value', 10));

        $this->assertTrue($cache->exists('foo'));
        $this->assertEquals('A basic value', $cache->retrieve('foo')->value());
        $this->assertEquals($time, $cache->created('foo'));
        $this->assertEquals($time + 600, $cache->expires('foo'));

        $this->assertTrue($cache->remove('foo'));
        $this->assertFalse($cache->exists('foo'));
        $this->assertNull($cache->retrieve('foo'));

        $this->assertFalse($cache->remove('doesnotexist'));
    }

    /**
     * @covers ::set
     * @covers ::retrieve
     * @covers ::remove
     */
    public function testOperationsWithMultipleInstances()
    {
        $cache1 = new MemoryCache();
        $cache2 = new MemoryCache();

        $time = time();
        $this->assertTrue($cache1->set('foo', 'A basic value', 10));

        $this->assertTrue($cache1->exists('foo'));
        $this->assertFalse($cache2->exists('foo'));
        $this->assertEquals('A basic value', $cache1->retrieve('foo')->value());
        $this->assertEquals($time, $cache1->created('foo'));
        $this->assertEquals($time + 600, $cache1->expires('foo'));

        $this->assertTrue($cache2->set('foo', 'Another basic value'));
        $this->assertTrue($cache2->exists('foo'));

        $this->assertEquals('A basic value', $cache1->retrieve('foo')->value());
        $this->assertTrue($cache1->remove('foo'));
        $this->assertFalse($cache1->exists('foo'));
        $this->assertNull($cache1->retrieve('foo'));
        $this->assertTrue($cache2->exists('foo'));
        $this->assertEquals('Another basic value', $cache2->retrieve('foo')->value());
    }

    /**
     * @covers ::flush
     */
    public function testFlush()
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

    /**
     * @covers ::flush
     */
    public function testFlushWithMultipleInstances()
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
}
