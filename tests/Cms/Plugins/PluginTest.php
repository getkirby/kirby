<?php

namespace Kirby\Cms;

class PluginTest extends TestCase
{
    public function testProps()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__,
        ]);

        $this->assertEquals('getkirby/test-plugin', $plugin->name());
        $this->assertEquals(__DIR__, $plugin->root());
        $this->assertEquals([], $plugin->extends());
    }

    public function testNameWithValidInput()
    {
        $plugin = new Plugin($name = 'abc-1234/DEF-56789', []);

        $this->assertEquals($name, $plugin->name());
    }

    public function testNameWithInvalidInput()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Plugin('äöü/!!!', []);
    }

    public function testManifest()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__
        ]);

        $this->assertEquals(__DIR__ . '/composer.json', $plugin->manifest());
    }

    public function testEmptyInfo()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__
        ]);

        $this->assertEquals([], $plugin->info());
    }

    public function testInfo()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__ . '/fixtures/plugin'
        ]);

        $authors = [
            [
                'name'  => 'Bastian Allgeier',
                'email' => 'bastian@getkirby.com'
            ]
        ];

        $this->assertEquals('getkirby/test-plugin', $plugin->info()['name']);
        $this->assertEquals('MIT', $plugin->info()['license']);
        $this->assertEquals('1.0.0', $plugin->info()['version']);
        $this->assertEquals('plugin', $plugin->info()['type']);
        $this->assertEquals('Some really nice description', $plugin->info()['description']);
        $this->assertEquals($authors, $plugin->info()['authors']);
    }

    public function testMagicCaller()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__ . '/fixtures/plugin'
        ]);

        $this->assertEquals('1.0.0', $plugin->version());
        $this->assertEquals('MIT', $plugin->license());
    }

    public function testPrefix()
    {
        $plugin = new Plugin('getkirby/test-plugin', []);

        $this->assertEquals('getkirby.test-plugin', $plugin->prefix());
    }

    public function testPrefixedOptions()
    {
        App::destroy();

        App::plugin('developer/plugin', [
            'options' => [
                'foo' => 'bar'
            ]
        ]);

        $app = new App();
        $this->assertEquals('bar', $app->option('developer.plugin.foo'));

        App::destroy();
    }
}
