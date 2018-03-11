<?php

namespace Kirby\Cms;

use Kirby\Cache\Cache;
use Kirby\Cache\FileCache;

class AppCachesTest extends TestCase
{

    public function testDisabledCache()
    {
        $kirby = new App();
        $this->assertEquals(Cache::class, get_class($kirby->cache('pages')));
    }

    public function testEnabledCacheWithoutOptions()
    {
        $kirby = new App([
            'options' => [
                'cache.pages' => true
            ]
        ]);

        $this->assertInstanceOf(FileCache::class, $kirby->cache('pages'));
    }

    public function testEnabledCacheWithOptions()
    {
        $kirby = new App([
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
        App::plugin([
            'name' => 'developer/plugin',
            'extends' => [
                'options' => [
                    'cache' => true
                ]
            ]
        ]);

        $kirby = new App();

        $this->assertInstanceOf(FileCache::class, $kirby->cache('developer.plugin'));
    }

}
