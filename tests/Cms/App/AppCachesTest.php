<?php

namespace Kirby\Cms;

use Kirby\Cache\FileCache;
use Kirby\Cache\NullCache;
use Kirby\Filesystem\Dir;

class AppCachesTest extends TestCase
{
    public function app(array $props = [])
    {
        return new App(array_merge([
            'roots' => [
                'index' => __DIR__ . '/fixtures/AppCachesTest',
            ]
        ], $props));
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Dir::remove(__DIR__ . '/fixtures/AppCachesTest');
    }

    public function testDisabledCache()
    {
        $this->assertEquals(NullCache::class, get_class($this->app()->cache('pages')));
    }

    public function testEnabledCacheWithoutOptions()
    {
        $kirby = $this->app([
            'options' => [
                'cache.pages' => true
            ]
        ]);

        $this->assertInstanceOf(FileCache::class, $kirby->cache('pages'));
        $this->assertEquals($kirby->root('cache'), $kirby->cache('pages')->options()['root']);
    }

    public function testEnabledCacheWithOptions()
    {
        $kirby = $this->app([
            'urls' => [
                'index' => 'https://getkirby.com/test'
            ],
            'options' => [
                'cache.pages' => [
                    'type' => 'file',
                    'root' => $root = __DIR__ . '/fixtures/AppCachesTest/cache'
                ]
            ]
        ]);

        $this->assertInstanceOf(FileCache::class, $kirby->cache('pages'));
        $this->assertEquals($root, $kirby->cache('pages')->options()['root']);

        $kirby->cache('pages')->set('home', 'test');
        $this->assertFileExists($root . '/getkirby.com_test/pages/home.cache');
    }

    public function testEnabledCacheWithOptionsAndPortPrefix()
    {
        $kirby = $this->app([
            'urls' => [
                'index' => 'http://127.0.0.1:8000'
            ],
            'options' => [
                'cache.pages' => [
                    'type' => 'file',
                    'root' => $root = __DIR__ . '/fixtures/AppCachesTest/cache'
                ]
            ]
        ]);

        $this->assertInstanceOf(FileCache::class, $kirby->cache('pages'));
        $this->assertEquals($root, $kirby->cache('pages')->options()['root']);

        $kirby->cache('pages')->set('home', 'test');
        $this->assertFileExists($root . '/127.0.0.1_8000/pages/home.cache');
    }

    public function testPluginDefaultCache()
    {
        App::plugin('developer/plugin', [
            'options' => [
                'cache' => true
            ]
        ]);

        $this->assertInstanceOf(FileCache::class, $this->app()->cache('developer.plugin'));
    }

    public function testPluginCustomCache()
    {
        App::plugin('developer/plugin', [
            'options' => [
                'cache.api' => true
            ]
        ]);

        $this->assertInstanceOf(FileCache::class, $this->app()->cache('developer.plugin.api'));
    }

    public function testDefaultCacheTypeClasses()
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
