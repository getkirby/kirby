<?php

namespace Kirby\Cache;

use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionMethod;

#[CoversClass(FileCache::class)]
class FileCacheTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cache.FileCache';

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testConstruct(): void
	{
		$cache = new FileCache([
			'root' => $root = static::TMP
		]);

		$this->assertSame($root, $cache->root());
		$this->assertDirectoryExists($root);
	}

	public function testConstructWithPrefix(): void
	{
		$cache = new FileCache([
			'root'   => $root = static::TMP,
			'prefix' => 'test'
		]);

		$this->assertSame($root . '/test', $cache->root());
		$this->assertDirectoryExists($root . '/test');
	}

	public function testEnabled(): void
	{
		$cache = new FileCache([
			'root' => static::TMP
		]);

		$this->assertTrue($cache->enabled());
	}

	public function testEnabledNotWritable(): void
	{
		$cache = new FileCache([
			'root' => $root = static::TMP
		]);

		chmod($root, 0o444);

		$this->assertFalse($cache->enabled());
	}

	public function testFile(): void
	{
		$method = new ReflectionMethod(FileCache::class, 'file');
		$method->setAccessible(true);

		$cache = new FileCache([
			'root' => $root = static::TMP
		]);
		$this->assertSame($root . '/test', $method->invoke($cache, 'test'));

		$cache = new FileCache([
			'root'      => $root = static::TMP,
			'extension' => 'cache'
		]);
		$this->assertSame($root . '/test.cache', $method->invoke($cache, 'test'));

		$cache = new FileCache([
			'root'   => $root = static::TMP,
			'prefix' => 'test1'
		]);
		$this->assertSame($root . '/test1/test', $method->invoke($cache, 'test'));

		$cache = new FileCache([
			'root'      => $root = static::TMP,
			'prefix'    => 'test1',
			'extension' => 'cache'
		]);
		$this->assertSame($root . '/test1/test.cache', $method->invoke($cache, 'test'));

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame($root . '/_empty/test', $method->invoke($cache, '/test'));

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame($root . '/test/_empty', $method->invoke($cache, 'test/'));

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame($root . '/test/_backslash/foo/bar', $method->invoke($cache, 'test\\foo/bar'));

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame($root . '/test/_backslash/_empty/foo/_backslash/bar', $method->invoke($cache, 'test\\/foo\\bar'));

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame($root . '/_empty/test/_empty', $method->invoke($cache, '/test/'));

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame(
			$root . '/_9d891e731f75deae56884d79e9816736b7488080/_9d891e731f75deae56884d79e9816736b7488080/test',
			$method->invoke($cache, '../../test')
		);

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame(
			$root . '/_9d891e731f75deae56884d79e9816736b7488080/test-cache_4caff0c1d0c8eb128ed9896b4b0258ef2848816b',
			$method->invoke($cache, '../test.cache')
		);

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame($root . '/_3a52ce780950d4d969792a2559cd519d7ee8c727/test-page', $method->invoke($cache, './test-page'));

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame(
			$root . '/_3a52ce780950d4d969792a2559cd519d7ee8c727/test-cache_4caff0c1d0c8eb128ed9896b4b0258ef2848816b',
			$method->invoke($cache, './test.cache')
		);

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame(
			$root . '/_9d891e731f75deae56884d79e9816736b7488080/pages/test/_empty',
			$method->invoke($cache, '../pages/test/')
		);

		$cache = new FileCache([
			'root' => $root = static::TMP,
		]);
		$this->assertSame(
			$root . '/_9d891e731f75deae56884d79e9816736b7488080/pages/test-cache_4caff0c1d0c8eb128ed9896b4b0258ef2848816b',
			$method->invoke($cache, '../pages/test.cache')
		);

		$cache = new FileCache([
			'root'      => $root = static::TMP,
			'extension' => 'cache'
		]);
		$this->assertSame(
			$root . '/_9d891e731f75deae56884d79e9816736b7488080/pages/test.cache',
			$method->invoke($cache, '../pages/test')
		);

		$cache = new FileCache([
			'root'   => $root = static::TMP,
			'prefix' => 'prefix'
		]);
		$this->assertSame(
			$root . '/prefix/_9d891e731f75deae56884d79e9816736b7488080/pages/test',
			$method->invoke($cache, '../pages/test')
		);

		$cache = new FileCache([
			'root'      => $root = static::TMP,
			'prefix'    => 'prefix',
			'extension' => 'cache'
		]);
		$this->assertSame(
			$root . '/prefix/_9d891e731f75deae56884d79e9816736b7488080/pages/test.cache',
			$method->invoke($cache, '../pages/test')
		);
		$this->assertSame(
			$root . '/prefix/_9d891e731f75deae56884d79e9816736b7488080/pages/test-invalid_76a6bcc476cffdcb56321fbbb4edfd19fece82c6.cache',
			$method->invoke($cache, '../pages/test.invalid')
		);
	}

	public function testOperations(): void
	{
		$cache = new FileCache([
			'root' => $root = static::TMP
		]);

		$time = time();
		$this->assertTrue($cache->set('foo', 'A basic value', 10));
		touch($root . '/foo', $time);

		$this->assertFileExists($root . '/foo');
		$this->assertTrue($cache->exists('foo'));
		$this->assertSame('A basic value', $cache->retrieve('foo')->value());
		$this->assertSame($time, $cache->created('foo'));
		$this->assertSame($time + 600, $cache->expires('foo'));

		$this->assertTrue($cache->remove('foo'));
		$this->assertFileDoesNotExist($root . '/foo');
		$this->assertFalse($cache->exists('foo'));
		$this->assertNull($cache->retrieve('foo'));

		$this->assertDirectoryDoesNotExist($root . '/bar');
		$this->assertTrue($cache->set('bar/baz', 'Another basic value', 10));
		$this->assertFileExists($root . '/bar/baz');
		$this->assertSame('Another basic value', $cache->retrieve('bar/baz')->value());

		$this->assertFalse($cache->remove('doesnotexist'));
	}

	public function testOperationsWithExtension(): void
	{
		$cache = new FileCache([
			'root'      => $root = static::TMP,
			'extension' => 'cache'
		]);

		$time = time();
		$this->assertTrue($cache->set('foo', 'A basic value', 10));
		touch($root . '/foo.cache', $time);

		$this->assertFileExists($root . '/foo.cache');
		$this->assertTrue($cache->exists('foo'));
		$this->assertSame('A basic value', $cache->retrieve('foo')->value());
		$this->assertSame($time, $cache->created('foo'));
		$this->assertSame($time + 600, $cache->expires('foo'));

		$this->assertDirectoryDoesNotExist($root . '/bar');
		$this->assertTrue($cache->set('bar/baz', 'Another basic value', 10));
		$this->assertFileExists($root . '/bar/baz.cache');
		$this->assertSame('Another basic value', $cache->retrieve('bar/baz')->value());

		$this->assertTrue($cache->remove('foo'));
		$this->assertFileDoesNotExist($root . '/foo.cache');
		$this->assertFalse($cache->exists('foo'));
		$this->assertNull($cache->retrieve('foo'));
	}

	public function testOperationsWithPrefix(): void
	{
		$cache1 = new FileCache([
			'root' => $root = static::TMP,
			'prefix' => 'test1'
		]);
		$cache2 = new FileCache([
			'root' => $root = static::TMP,
			'prefix' => 'test2'
		]);

		$time = time();
		$this->assertTrue($cache1->set('foo', 'A basic value', 10));
		touch($root . '/test1/foo', $time);

		$this->assertFileExists($root . '/test1/foo');
		$this->assertTrue($cache1->exists('foo'));
		$this->assertFalse($cache2->exists('foo'));
		$this->assertSame('A basic value', $cache1->retrieve('foo')->value());
		$this->assertSame($time, $cache1->created('foo'));
		$this->assertSame($time + 600, $cache1->expires('foo'));

		$this->assertTrue($cache2->set('foo', 'Another basic value'));
		touch($root . '/test2/foo', $time);
		$this->assertTrue($cache2->exists('foo'));

		$this->assertDirectoryDoesNotExist($root . '/test1/bar');
		$this->assertTrue($cache1->set('bar/baz', 'Another basic value', 10));
		$this->assertFileExists($root . '/test1/bar/baz');
		$this->assertSame('Another basic value', $cache1->retrieve('bar/baz')->value());

		$this->assertSame('A basic value', $cache1->retrieve('foo')->value());
		$this->assertTrue($cache1->remove('foo'));
		$this->assertFileDoesNotExist($root . '/test1/foo');
		$this->assertFalse($cache1->exists('foo'));
		$this->assertNull($cache1->retrieve('foo'));
		$this->assertTrue($cache2->exists('foo'));
		$this->assertSame('Another basic value', $cache2->retrieve('foo')->value());
	}

	public function testFlush(): void
	{
		$cache = new FileCache([
			'root' => $root = static::TMP
		]);

		$cache->set('a', 'A basic value');
		$cache->set('b', 'A basic value');
		$cache->set('c', 'A basic value');
		$cache->set('d/a', 'A basic value');
		$this->assertFileExists($root . '/a');
		$this->assertFileExists($root . '/b');
		$this->assertFileExists($root . '/c');
		$this->assertFileExists($root . '/d/a');

		$this->assertTrue($cache->flush());
		$this->assertFileDoesNotExist($root . '/a');
		$this->assertFileDoesNotExist($root . '/b');
		$this->assertFileDoesNotExist($root . '/c');
		$this->assertDirectoryDoesNotExist($root . '/d');
	}

	public function testFlushWithPrefix(): void
	{
		$cache1 = new FileCache([
			'root' => $root = static::TMP,
			'prefix' => 'test1'
		]);
		$cache2 = new FileCache([
			'root' => $root = static::TMP,
			'prefix' => 'test2'
		]);

		$cache1->set('a', 'A basic value');
		$cache1->set('b', 'A basic value');
		$cache2->set('a', 'A basic value');
		$cache2->set('b', 'A basic value');
		$cache1->set('c/a', 'A basic value');
		$cache2->set('c/a', 'A basic value');
		$this->assertFileExists($root . '/test1/a');
		$this->assertFileExists($root . '/test1/b');
		$this->assertFileExists($root . '/test2/a');
		$this->assertFileExists($root . '/test2/b');
		$this->assertFileExists($root . '/test2/c/a');
		$this->assertFileExists($root . '/test2/c/a');

		$this->assertTrue($cache1->flush());
		$this->assertFileDoesNotExist($root . '/test1/a');
		$this->assertFileDoesNotExist($root . '/test1/b');
		$this->assertDirectoryDoesNotExist($root . '/test1/c');
		$this->assertFileExists($root . '/test2/a');
		$this->assertFileExists($root . '/test2/b');
		$this->assertFileExists($root . '/test2/c/a');
	}

	public function testRemoveEmptyDirectories(): void
	{
		$cache = new FileCache([
			'root'      => $root = static::TMP,
			'extension' => 'cache'
		]);

		// set & retrieve
		$this->assertDirectoryDoesNotExist($root . '/foo/bar/baz');
		$this->assertTrue($cache->set('foo/bar/baz/test', 'Another basic value', 10));
		$this->assertFileExists($root . '/foo/bar/baz/test.cache');
		$this->assertSame('Another basic value', $cache->retrieve('foo/bar/baz/test')->value());

		// remove
		$this->assertTrue($cache->remove('foo/bar/baz/test'));
		$this->assertDirectoryDoesNotExist($root . '/foo/bar/baz');
		$this->assertDirectoryDoesNotExist($root . '/foo/bar');
		$this->assertDirectoryDoesNotExist($root . '/foo');
		$this->assertDirectoryExists($root);
	}

	public function testRemoveEmptyDirectoriesWithNotEmptyDirs(): void
	{
		$cache = new FileCache([
			'root'      => $root = static::TMP,
			'extension' => 'cache'
		]);

		// set & retrieve
		$this->assertDirectoryDoesNotExist($root . '/foo/bar/baz');
		$this->assertTrue($cache->set('foo/bar/baz/test', 'Value A', 10));
		$this->assertTrue($cache->set('foo/test', 'Value B', 10));
		$this->assertFileExists($root . '/foo/bar/baz/test.cache');
		$this->assertFileExists($root . '/foo/test.cache');
		$this->assertSame('Value A', $cache->retrieve('foo/bar/baz/test')->value());
		$this->assertSame('Value B', $cache->retrieve('foo/test')->value());

		// remove
		$this->assertTrue($cache->remove('foo/bar/baz/test'));
		$this->assertDirectoryDoesNotExist($root . '/foo/bar/baz');
		$this->assertDirectoryDoesNotExist($root . '/foo/bar');
		$this->assertDirectoryExists($root . '/foo');
		$this->assertDirectoryExists($root);
	}
}
