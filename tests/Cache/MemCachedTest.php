<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Cache\MemCached
 */
class MemCachedTest extends TestCase
{
    public function setUp(): void
    {
        if (class_exists('Memcached') === false) {
            $this->markTestSkipped('The Memcached extension is not available.');
            return;
        }

        $connection = new \Memcached();
        $connection->addServer('localhost', 11211);
        if (is_array($connection->getStats()) !== true) {
            $this->markTestSkipped('The Memcached server is not running.');
        }
    }

    public function tearDown(): void
    {
        $connection = new \Memcached();
        $connection->addServer('localhost', 11211);
        $connection->flush();
    }

    /**
     * @covers ::set
     * @covers ::retrieve
     * @covers ::remove
     */
    public function testOperations()
    {
        $cache = new MemCached([]);

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
    public function testOperationsWithPrefix()
    {
        $cache1 = new MemCached([
            'prefix' => 'test1'
        ]);
        $cache2 = new MemCached([
            'prefix' => 'test2'
        ]);

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
     * @covers ::__construct
     */
    public function testConstructServer()
    {
        $cache = new MemCached([
            'host' => 'localhost',
            'port' => 1234
        ]);
        $this->assertFalse($cache->set('foo', 'A basic value'));
        $this->assertNull($cache->retrieve('foo'));
        $this->assertFalse($cache->remove('foo'));

        $cache = new MemCached([
            'host' => 'asdfgh.invalid',
            'port' => 11211
        ]);
        $this->assertFalse($cache->set('foo', 'A basic value'));
        $this->assertNull($cache->retrieve('foo'));
        $this->assertFalse($cache->remove('foo'));
    }

    /**
     * @covers ::flush
     */
    public function testFlush()
    {
        $cache = new MemCached([]);

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
}
