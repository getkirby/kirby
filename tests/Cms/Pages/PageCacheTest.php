<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
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
                        'slug'     => 'b',
                        'template' => 'expiry'
                    ],
                    [
                        'slug'     => 'c',
                        'template' => 'disabled'
                    ]
                ]
            ],
            'options' => [
                'cache.pages' => true
            ]
        ]);

        Dir::make($this->fixtures);
        F::write(
            $this->fixtures . '/site/templates/default.php',
            'This is a test: <?= uniqid() ?>'
        );
        F::write(
            $this->fixtures . '/site/templates/disabled.php',
            'This is a test: <?= uniqid() ?><?php $kirby->response()->cache(false); ?>'
        );
        F::write(
            $this->fixtures . '/site/templates/expiry.php',
            '<?php $time = random_int(1000000000, 2000000000); $kirby->response()->expires($time); echo $time;'
        );
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

    public function testRenderCache()
    {
        $cache = $this->app->cache('pages');
        $page  = $this->app->page('a');

        $this->assertNull($cache->retrieve('a.html'));

        $html1 = $page->render();
        $this->assertStringStartsWith('This is a test:', $html1);

        $value = $cache->retrieve('a.html');
        $this->assertInstanceOf('Kirby\Cache\Value', $value);
        $this->assertSame($html1, $value->value()['html']);
        $this->assertNull($value->expires());

        $html2 = $page->render();
        $this->assertSame($html1, $html2);
    }

    public function testRenderCacheCustomExpiry()
    {
        $cache = $this->app->cache('pages');
        $page  = $this->app->page('b');

        $this->assertNull($cache->retrieve('b.html'));

        $time = $page->render();

        $value = $cache->retrieve('b.html');
        $this->assertInstanceOf('Kirby\Cache\Value', $value);
        $this->assertSame($time, $value->value()['html']);
        $this->assertSame((int)$time, $value->expires());
    }

    public function testRenderCacheDisabled()
    {
        $cache = $this->app->cache('pages');
        $page  = $this->app->page('c');

        $this->assertNull($cache->retrieve('c.html'));

        $html1 = $page->render();
        $this->assertStringStartsWith('This is a test:', $html1);

        $this->assertNull($cache->retrieve('c.html'));

        $html2 = $page->render();
        $this->assertNotSame($html1, $html2);
    }
}
