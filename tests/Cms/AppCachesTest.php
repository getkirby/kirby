<?php

namespace Kirby\Cms;

use Kirby\Cache\FileCache;
use Kirby\Cache\NullCache;

class AppCachesTest extends TestCase
{
    public function app(array $props = [])
    {
        return new App(array_merge([
            'roots' => [
                'index' => __DIR__
            ]
        ], $props));
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
    }

    public function testEnabledCacheWithOptions()
    {
        $kirby = $this->app([
            'options' => [
                'cache.pages' => [
                    'type' => 'file',
                    'root' => __DIR__ . '/fixtures/cache'
                ]
            ]
        ]);

        $this->assertInstanceOf(FileCache::class, $kirby->cache('pages'));
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
