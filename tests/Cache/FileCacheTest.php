<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Kirby\Toolkit\Dir;

/**
 * @coversDefaultClass \Kirby\Cache\FileCache
 */
class FileCacheTest extends TestCase
{
    public function tearDown()
    {
        Dir::remove(__DIR__ . '/fixtures/file');
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $cache = new FileCache([
            'root' => $root = __DIR__ . '/fixtures/file'
        ]);

        $this->assertDirectoryExists($root);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructWithPrefix()
    {
        $cache = new FileCache([
            'root'   => $root = __DIR__ . '/fixtures/file',
            'prefix' => 'test'
        ]);

        $this->assertDirectoryExists($root . '/test');
    }

    /**
     * @covers ::file
     */
    public function testFile()
    {
        $method = new ReflectionMethod(FileCache::class, 'file');
        $method->setAccessible(true);

        $cache = new FileCache([
            'root' => $root = __DIR__ . '/fixtures/file'
        ]);
        $this->assertEquals($root . '/test', $method->invoke($cache, 'test'));

        $cache = new FileCache([
            'root'      => $root = __DIR__ . '/fixtures/file',
            'extension' => 'cache'
        ]);
        $this->assertEquals($root . '/test.cache', $method->invoke($cache, 'test'));

        $cache = new FileCache([
            'root'   => $root = __DIR__ . '/fixtures/file',
            'prefix' => 'test1'
        ]);
        $this->assertEquals($root . '/test1/test', $method->invoke($cache, 'test'));

        $cache = new FileCache([
            'root'      => $root = __DIR__ . '/fixtures/file',
            'prefix'    => 'test1',
            'extension' => 'cache'
        ]);
        $this->assertEquals($root . '/test1/test.cache', $method->invoke($cache, 'test'));
    }

    /**
     * @covers ::set
     * @covers ::created
     * @covers ::retrieve
     * @covers ::remove
     */
    public function testOperations()
    {
        $cache = new FileCache([
            'root' => $root = __DIR__ . '/fixtures/file'
        ]);

        $time = time();
        $this->assertTrue($cache->set('foo', 'A basic value', 10));

        $this->assertFileExists($root . '/foo');
        $this->assertTrue($cache->exists('foo'));
        $this->assertEquals('A basic value', $cache->retrieve('foo')->value());
        $this->assertEquals($time, $cache->created('foo'));
        $this->assertEquals($time + 600, $cache->expires('foo'));

        $this->assertTrue($cache->remove('foo'));
        $this->assertFileNotExists($root . '/foo');
        $this->assertFalse($cache->exists('foo'));
        $this->assertNull($cache->retrieve('foo'));

        $this->assertFalse($cache->remove('doesnotexist'));
    }

    /**
     * @covers ::set
     * @covers ::created
     * @covers ::retrieve
     * @covers ::remove
     */
    public function testOperationsWithExtension()
    {
        $cache = new FileCache([
            'root'      => $root = __DIR__ . '/fixtures/file',
            'extension' => 'cache'
        ]);

        $time = time();
        $this->assertTrue($cache->set('foo', 'A basic value', 10));

        $this->assertFileExists($root . '/foo.cache');
        $this->assertTrue($cache->exists('foo'));
        $this->assertEquals('A basic value', $cache->retrieve('foo')->value());
        $this->assertEquals($time, $cache->created('foo'));
        $this->assertEquals($time + 600, $cache->expires('foo'));

        $this->assertTrue($cache->remove('foo'));
        $this->assertFileNotExists($root . '/foo.cache');
        $this->assertFalse($cache->exists('foo'));
        $this->assertNull($cache->retrieve('foo'));
    }

    /**
     * @covers ::set
     * @covers ::created
     * @covers ::retrieve
     * @covers ::remove
     */
    public function testOperationsWithPrefix()
    {
        $cache1 = new FileCache([
            'root' => $root = __DIR__ . '/fixtures/file',
            'prefix' => 'test1'
        ]);
        $cache2 = new FileCache([
            'root' => $root = __DIR__ . '/fixtures/file',
            'prefix' => 'test2'
        ]);

        $time = time();
        $this->assertTrue($cache1->set('foo', 'A basic value', 10));

        $this->assertFileExists($root . '/test1/foo');
        $this->assertTrue($cache1->exists('foo'));
        $this->assertFalse($cache2->exists('foo'));
        $this->assertEquals('A basic value', $cache1->retrieve('foo')->value());
        $this->assertEquals($time, $cache1->created('foo'));
        $this->assertEquals($time + 600, $cache1->expires('foo'));

        $this->assertTrue($cache2->set('foo', 'Another basic value'));
        $this->assertTrue($cache2->exists('foo'));

        $this->assertEquals('A basic value', $cache1->retrieve('foo')->value());
        $this->assertTrue($cache1->remove('foo'));
        $this->assertFileNotExists($root . '/test1/foo');
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
        $cache = new FileCache([
            'root' => $root = __DIR__ . '/fixtures/file'
        ]);

        $cache->set('a', 'A basic value');
        $cache->set('b', 'A basic value');
        $cache->set('c', 'A basic value');
        $this->assertFileExists($root . '/a');
        $this->assertFileExists($root . '/b');
        $this->assertFileExists($root . '/c');

        $this->assertTrue($cache->flush());
        $this->assertFileNotExists($root . '/a');
        $this->assertFileNotExists($root . '/b');
        $this->assertFileNotExists($root . '/c');
    }

    /**
     * @covers ::flush
     */
    public function testFlushWithPrefix()
    {
        $cache1 = new FileCache([
            'root' => $root = __DIR__ . '/fixtures/file',
            'prefix' => 'test1'
        ]);
        $cache2 = new FileCache([
            'root' => $root = __DIR__ . '/fixtures/file',
            'prefix' => 'test2'
        ]);

        $cache1->set('a', 'A basic value');
        $cache1->set('b', 'A basic value');
        $cache2->set('a', 'A basic value');
        $cache2->set('b', 'A basic value');
        $this->assertFileExists($root . '/test1/a');
        $this->assertFileExists($root . '/test1/b');
        $this->assertFileExists($root . '/test2/a');
        $this->assertFileExists($root . '/test2/b');

        $this->assertTrue($cache1->flush());
        $this->assertFileNotExists($root . '/test1/a');
        $this->assertFileNotExists($root . '/test1/b');
        $this->assertFileExists($root . '/test2/a');
        $this->assertFileExists($root . '/test2/b');
    }
}
