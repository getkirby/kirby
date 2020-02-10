<?php

namespace Kirby\Cache;

use Kirby\Toolkit\Dir;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @coversDefaultClass \Kirby\Cache\FileCache
 */
class FileCacheTest extends TestCase
{
    public function tearDown(): void
    {
        Dir::remove(__DIR__ . '/fixtures/file');
    }

    /**
     * @covers ::__construct
     * @covers ::root
     */
    public function testConstruct()
    {
        $cache = new FileCache([
            'root' => $root = __DIR__ . '/fixtures/file'
        ]);

        $this->assertSame($root, $cache->root());
        $this->assertDirectoryExists($root);
    }

    /**
     * @covers ::__construct
     * @covers ::root
     */
    public function testConstructWithPrefix()
    {
        $cache = new FileCache([
            'root'   => $root = __DIR__ . '/fixtures/file',
            'prefix' => 'test'
        ]);

        $this->assertSame($root . '/test', $cache->root());
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
        $this->assertSame($root . '/test', $method->invoke($cache, 'test'));

        $cache = new FileCache([
            'root'      => $root = __DIR__ . '/fixtures/file',
            'extension' => 'cache'
        ]);
        $this->assertSame($root . '/test.cache', $method->invoke($cache, 'test'));

        $cache = new FileCache([
            'root'   => $root = __DIR__ . '/fixtures/file',
            'prefix' => 'test1'
        ]);
        $this->assertSame($root . '/test1/test', $method->invoke($cache, 'test'));

        $cache = new FileCache([
            'root'      => $root = __DIR__ . '/fixtures/file',
            'prefix'    => 'test1',
            'extension' => 'cache'
        ]);
        $this->assertSame($root . '/test1/test.cache', $method->invoke($cache, 'test'));
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
        $this->assertSame('A basic value', $cache->retrieve('foo')->value());
        $this->assertSame($time, $cache->created('foo'));
        $this->assertSame($time + 600, $cache->expires('foo'));

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
        $this->assertSame('A basic value', $cache->retrieve('foo')->value());
        $this->assertSame($time, $cache->created('foo'));
        $this->assertSame($time + 600, $cache->expires('foo'));

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
        $this->assertSame('A basic value', $cache1->retrieve('foo')->value());
        $this->assertSame($time, $cache1->created('foo'));
        $this->assertSame($time + 600, $cache1->expires('foo'));

        $this->assertTrue($cache2->set('foo', 'Another basic value'));
        $this->assertTrue($cache2->exists('foo'));

        $this->assertSame('A basic value', $cache1->retrieve('foo')->value());
        $this->assertTrue($cache1->remove('foo'));
        $this->assertFileNotExists($root . '/test1/foo');
        $this->assertFalse($cache1->exists('foo'));
        $this->assertNull($cache1->retrieve('foo'));
        $this->assertTrue($cache2->exists('foo'));
        $this->assertSame('Another basic value', $cache2->retrieve('foo')->value());
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
