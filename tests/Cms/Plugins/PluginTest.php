<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass Kirby\Cms\Plugin
 */
class PluginTest extends TestCase
{
    public function setUp(): void
    {
        App::destroy();
    }

    public function tearDown(): void
    {
        App::destroy();
    }

    /**
     * @covers ::__call
     */
    public function test__call()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__ . '/fixtures/plugin'
        ]);

        $this->assertSame('1.0.0', $plugin->version());
        $this->assertSame('MIT', $plugin->license());
    }

    /**
     * @covers ::authors
     */
    public function testAuthors()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__ . '/fixtures/plugin'
        ]);

        $authors = [
            [
                'name'  => 'A',
                'email' => 'a@getkirby.com'
            ],
            [
                'name'  => 'B',
                'email' => 'b@getkirby.com'
            ]
        ];

        $this->assertSame($authors, $plugin->authors());
    }

    /**
     * @covers ::authorsNames
     */
    public function testAuthorsNames()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__ . '/fixtures/plugin'
        ]);

        $this->assertSame('A, B', $plugin->authorsNames());
    }

    /**
     * @covers ::extends
     */
    public function testExtends()
    {
        $plugin = new Plugin('getkirby/test-plugin', $extends = [
            'fields' => [
                'test' => []
            ]
        ]);

        $this->assertSame($extends, $plugin->extends());
    }

    /**
     * @covers ::id
     */
    public function testId()
    {
        $plugin = new Plugin($id = 'abc-1234/DEF-56789', []);

        $this->assertSame($id, $plugin->id());
    }

    /**
     * @covers ::info
     */
    public function testInfo()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__ . '/fixtures/plugin'
        ]);

        $authors = [
            [
                'name'  => 'A',
                'email' => 'a@getkirby.com'
            ],
            [
                'name'  => 'B',
                'email' => 'b@getkirby.com'
            ]
        ];

        $this->assertSame('getkirby/test-plugin', $plugin->info()['name']);
        $this->assertSame('MIT', $plugin->info()['license']);
        $this->assertSame('1.0.0', $plugin->info()['version']);
        $this->assertSame('plugin', $plugin->info()['type']);
        $this->assertSame('Some really nice description', $plugin->info()['description']);
        $this->assertSame($authors, $plugin->info()['authors']);
    }

    /**
     * @covers ::info
     */
    public function testInfoFromProps()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'info' => [
                'license' => 'MIT'
            ]
        ]);

        $this->assertSame('MIT', $plugin->info()['license']);
    }

    /**
     * @covers ::info
     */
    public function testInfoWhenEmpty()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__
        ]);

        $this->assertSame([], $plugin->info());
    }

    /**
     * @covers ::link
     */
    public function testLinkFromHomepage()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'info' => [
                'homepage' => 'https://getkirby.com'
            ]
        ]);

        $this->assertSame('https://getkirby.com', $plugin->link());
    }

    /**
     * @covers ::link
     */
    public function testLinkFromInvalidHomepage()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'info' => [
                'homepage' => 'test'
            ]
        ]);

        $this->assertNull($plugin->link());
    }

    /**
     * @covers ::link
     */
    public function testLinkFromSupportDocs()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'info' => [
                'support' => [
                    'docs' => 'https://getkirby.com'
                ]
            ]
        ]);

        $this->assertSame('https://getkirby.com', $plugin->link());
    }

    /**
     * @covers ::link
     */
    public function testLinkFromSupportSource()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'info' => [
                'support' => [
                    'source' => 'https://getkirby.com'
                ]
            ]
        ]);

        $this->assertSame('https://getkirby.com', $plugin->link());
    }

    /**
     * @covers ::link
     */
    public function testLinkWhenEmpty()
    {
        $plugin = new Plugin('getkirby/test-plugin');
        $this->assertNull($plugin->link());
    }

    /**
     * @covers ::mediaRoot
     */
    public function testMediaRoot()
    {
        new App([
            'roots' => [
                'index' => '/dev/null',
                'media' => $media = __DIR__ . '/media'
            ]
        ]);

        $plugin = new Plugin('getkirby/test-plugin');

        $this->assertSame($media . '/plugins/getkirby/test-plugin', $plugin->mediaRoot());
    }

    /**
     * @covers ::mediaUrl
     */
    public function testMediaUrl()
    {
        new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'urls' => [
                'index' => '/'
            ]
        ]);

        $plugin = new Plugin('getkirby/test-plugin');

        $this->assertSame('/media/plugins/getkirby/test-plugin', $plugin->mediaUrl());
    }

    /**
     * @covers ::manifest
     */
    public function testManifest()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => __DIR__
        ]);

        $this->assertSame(__DIR__ . '/composer.json', $plugin->manifest());
    }

    /**
     * @covers ::name
     * @covers ::setName
     */
    public function testName()
    {
        $plugin = new Plugin($name = 'abc-1234/DEF-56789', []);

        $this->assertSame($name, $plugin->name());
    }

    /**
     * @covers ::name
     * @covers ::setName
     */
    public function testNameWithInvalidInput()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Plugin('äöü/!!!', []);
    }

    /**
     * @covers ::option
     */
    public function testOption()
    {
        App::plugin('developer/plugin', [
            'options' => [
                'foo' => 'bar'
            ]
        ]);

        $app = new App();

        $this->assertSame('bar', $app->plugin('developer/plugin')->option('foo'));
        $this->assertSame('bar', $app->option('developer.plugin.foo'));
    }

    /**
     * @covers ::prefix
     */
    public function testPrefix()
    {
        $plugin = new Plugin('getkirby/test-plugin', []);

        $this->assertSame('getkirby.test-plugin', $plugin->prefix());
    }

    /**
     * @covers ::root
     */
    public function testRoot()
    {
        $plugin = new Plugin('getkirby/test-plugin');

        $this->assertSame(__DIR__, $plugin->root());
    }

    /**
     * @covers ::root
     */
    public function testRootWithCustomSetup()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => $custom = __DIR__ . '/test',
        ]);

        $this->assertSame($custom, $plugin->root());
    }

    /**
     * @covers ::toArray
     */
    public function testToArray()
    {
        $plugin = new Plugin('getkirby/test-plugin', [
            'root' => $root = __DIR__ . '/fixtures/plugin'
        ]);

        $expected = [
            'authors' => [
                [ 'name' => 'A', 'email' => 'a@getkirby.com' ],
                [ 'name' => 'B', 'email' => 'b@getkirby.com' ]
            ],
            'description' => 'Some really nice description',
            'name'        => 'getkirby/test-plugin',
            'license'     => 'MIT',
            'link'        => 'https://getkirby.com',
            'root'        => $root,
            'version'     => '1.0.0'
        ];

        $this->assertSame($expected, $plugin->toArray());
    }
}
