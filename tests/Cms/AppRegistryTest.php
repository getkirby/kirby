<?php

namespace Kirby\Cms;

use Kirby\Image\Image;

class AppRegistryTest extends TestCase
{

    public function testCollection()
    {
        $kirby = new App;
        $pages = new Pages([]);
        $kirby->set('collection', 'test', function () use ($pages) {
            return $pages;
        });

        $this->assertEquals($pages, $kirby->collection('test'));
    }

    public function testController()
    {
        $kirby = new App;
        $kirby->set('controller', 'test', function () {
            return ['foo' => 'bar'];
        });

        $this->assertEquals(['foo' => 'bar'], $kirby->controller('test'));
    }

    public function testHooks()
    {
        $kirby    = new App;
        $phpUnit  = $this;
        $executed = 0;

        $kirby->set('hook', 'testHook', function ($message) use ($phpUnit, &$executed) {
            $phpUnit->assertEquals('test', $message);
            $executed++;
        });

        $kirby->set('hook', 'testHook', function ($message) use ($phpUnit, &$executed) {
            $phpUnit->assertEquals('test', $message);
            $executed++;
        });

        $kirby->hooks()->trigger('testHook', 'test');

        $this->assertEquals(2, $executed);
    }

    public function testOption()
    {
        $kirby = new App;
        $kirby->set('option', 'testOption', 'testValue');

        $this->assertEquals('testValue', $kirby->option('testOption'));
    }

}
