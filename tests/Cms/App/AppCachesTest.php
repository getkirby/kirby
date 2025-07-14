<?php

namespace Kirby\Cms;

use Kirby\Cache\FileCache;
use Kirby\Cache\NullCache;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;

class AppCachesTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.AppCaches';

	public function app(array $props = [])
	{
		return new App([
			'roots' => [
				'index' => static::TMP,
			],
			...$props
		]);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		Dir::remove(static::TMP);
	}

	public function testDisabledCache(): void
	{
		$this->assertInstanceOf(NullCache::class, $this->app()->cache('pages'));
	}

	public function testEnabledCacheWithoutOptions(): void
	{
		$kirby = $this->app([
			'options' => [
				'cache.pages' => true
			]
		]);

		$this->assertInstanceOf(FileCache::class, $kirby->cache('pages'));
		$this->assertSame($kirby->root('cache'), $kirby->cache('pages')->options()['root']);
	}

	public function testInvalidCacheType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid cache type "not-exists"');

		$kirby = $this->app([
			'options' => [
				'cache.pages' => [
					'type' => 'not-exists'
				]
			]
		]);

		$kirby->cache('pages');
	}

	public function testEnabledCacheWithOptions(): void
	{
		$kirby = $this->app([
			'urls' => [
				'index' => 'https://getkirby.com/test'
			],
			'options' => [
				'cache.pages' => [
					'type' => 'file',
					'root' => $root = static::TMP . '/cache'
				]
			]
		]);

		$this->assertInstanceOf(FileCache::class, $kirby->cache('pages'));
		$this->assertSame($root, $kirby->cache('pages')->options()['root']);

		$kirby->cache('pages')->set('home', 'test');
		$this->assertFileExists($root . '/getkirby.com_test/pages/home.cache');
	}

	public function testEnabledCacheWithOptionsAndPortPrefix(): void
	{
		$kirby = $this->app([
			'urls' => [
				'index' => 'http://127.0.0.1:8000'
			],
			'options' => [
				'cache.pages' => [
					'type' => 'file',
					'root' => $root = static::TMP . '/cache'
				]
			]
		]);

		$this->assertInstanceOf(FileCache::class, $kirby->cache('pages'));
		$this->assertSame($root, $kirby->cache('pages')->options()['root']);

		$kirby->cache('pages')->set('home', 'test');
		$this->assertFileExists($root . '/127.0.0.1_8000/pages/home.cache');
	}

	public function testPluginDefaultCache(): void
	{
		App::plugin('developer/plugin', [
			'options' => [
				'cache' => true
			]
		]);

		$this->assertInstanceOf(FileCache::class, $this->app()->cache('developer.plugin'));
	}

	public function testPluginCustomCache(): void
	{
		App::plugin('developer/plugin', [
			'options' => [
				'cache.api' => true
			]
		]);

		$this->assertInstanceOf(FileCache::class, $this->app()->cache('developer.plugin.api'));
	}

	public function testDefaultCacheTypeClasses(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$types = $app->extensions('cacheTypes');

		foreach ($types as $className) {
			$this->assertTrue(class_exists($className));
		}
	}
}
