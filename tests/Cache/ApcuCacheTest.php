<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;

class ApcuCacheTest extends TestCase
{
    public function testSetGetRemove()
    {
        $cache = new ApcuCache([]);

        $cache->set('foo', 'A basic value');

        $this->assertEquals('A basic value', $cache->get('foo'));
        $this->assertEquals(time(), $cache->created('foo'));

        $cache->remove('foo');
        $this->assertFalse($cache->exists('foo'));
    }

    public function testSetGetRemoveWithPrefix()
    {
        $cache = new ApcuCache([
            'prefix' => 'test'
        ]);

        $cache->set('foo', 'A basic value');

        $this->assertEquals('A basic value', $cache->get('foo'));
        $this->assertEquals(time(), $cache->created('foo'));

        $cache->remove('foo');
        $this->assertFalse($cache->exists('foo'));
    }

    public function testFlush()
    {
        $cache = new ApcuCache([]);

        $cache->set('a', 'A basic value');
        $cache->set('b', 'A basic value');
        $cache->set('c', 'A basic value');
        $this->assertTrue($cache->exists('a'));
        $this->assertTrue($cache->exists('b'));
        $this->assertTrue($cache->exists('c'));

        $cache->flush();
        $this->assertFalse($cache->exists('a'));
        $this->assertFalse($cache->exists('b'));
        $this->assertFalse($cache->exists('c'));
    }

    public function testFlushWithPrefix()
    {
        $cache = new ApcuCache([
            'prefix' => 'test'
        ]);

        $cache->set('a', 'A basic value');
        $cache->set('b', 'A basic value');
        $cache->set('c', 'A basic value');
        $this->assertTrue($cache->exists('a'));
        $this->assertTrue($cache->exists('b'));
        $this->assertTrue($cache->exists('c'));

        $cache->flush();
        $this->assertFalse($cache->exists('a'));
        $this->assertFalse($cache->exists('b'));
        $this->assertFalse($cache->exists('c'));
    }
}
