<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PageCacheTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/PageCacheTest'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a'
                    ],
                    [
                        'slug' => 'b'
                    ]
                ]
            ],
            'options' => [
                'cache.pages' => true
            ]
        ]);

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function requestMethodProvider()
    {
        return [
            ['GET', true],
            ['HEAD', true],
            ['POST', false],
            ['DELETE', false],
            ['PATCH', false],
            ['PUT', false],
        ];
    }

    /**
     * @dataProvider requestMethodProvider
     */
    public function testRequestMethod($method, $expected)
    {
        $app = $this->app->clone([
            'request' => [
                'method' => $method
            ]
        ]);

        $this->assertEquals($expected, $app->page('a')->isCacheable());
    }

    /**
     * @dataProvider requestMethodProvider
     */
    public function testRequestData($method)
    {
        $app = $this->app->clone([
            'request' => [
                'method' => $method,
                'query'  => ['foo' => 'bar']
            ]
        ]);

        $this->assertFalse($app->page('a')->isCacheable());
    }

    public function testRequestParams()
    {
        $app = $this->app->clone([
            'request' => [
                'url' => 'https://getkirby.com/blog/page:2'
            ]
        ]);

        $this->assertFalse($app->page('a')->isCacheable());
    }

    public function testIgnoreId()
    {
        $app = $this->app->clone([
            'options' => [
                'cache.pages' => [
                    'ignore' => [
                        'b'
                    ]
                ]
            ]
        ]);

        $this->assertTrue($app->page('a')->isCacheable());
        $this->assertFalse($app->page('b')->isCacheable());
    }

    public function testIgnoreCallback()
    {
        $app = $this->app->clone([
            'options' => [
                'cache.pages' => [
                    'ignore' => function ($page) {
                        return $page->id() === 'a';
                    }
                ]
            ]
        ]);

        $this->assertFalse($app->page('a')->isCacheable());
        $this->assertTrue($app->page('b')->isCacheable());
    }

    public function testDisabledCache()
    {
        // deactivate on top level
        $app = $this->app->clone([
            'options' => [
                'cache.pages' => false
            ]
        ]);

        $this->assertFalse($app->page('a')->isCacheable());

        // deactivate in array
        $app = $this->app->clone([
            'options' => [
                'cache.pages' => [
                    'active' => false
                ]
            ]
        ]);

        $this->assertFalse($app->page('a')->isCacheable());
    }
}
