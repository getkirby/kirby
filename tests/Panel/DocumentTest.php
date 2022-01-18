<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Document
 */
class DocumentTest extends TestCase
{
    protected $app;
    protected $tmp = __DIR__ . '/tmp';

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ]
        ]);

        Dir::make($this->tmp);
    }

    public function tearDown(): void
    {
        // clear session file first
        $this->app->session()->destroy();

        Dir::remove($this->tmp);

        // clear fake json requests
        $_GET = [];

        // clean up $_SERVER
        unset($_SERVER['SERVER_SOFTWARE']);
    }

    /**
     * @covers ::assets
     */
    public function testAssetsDefaults(): void
    {
        // default asset setup
        $assets  = Document::assets();
        $base    = '/media/panel/' . $this->app->versionHash();

        // css
        $this->assertSame($base . '/css/style.css', $assets['css']['index']);
        $this->assertSame('/media/plugins/index.css?0', $assets['css']['plugins']);

        // icons
        $this->assertSame($base . '/apple-touch-icon.png', $assets['icons']['apple-touch-icon']['url']);
        $this->assertSame($base . '/favicon.svg', $assets['icons']['shortcut icon']['url']);
        $this->assertSame($base . '/favicon.png', $assets['icons']['alternate icon']['url']);

        // js
        $this->assertSame($base . '/js/vendor.js', $assets['js']['vendor']['src']);
        $this->assertSame('module', $assets['js']['vendor']['type']);

        $this->assertSame($base . '/js/plugins.js', $assets['js']['pluginloader']['src']);
        $this->assertSame('module', $assets['js']['pluginloader']['type']);

        $this->assertSame('/media/plugins/index.js?0', $assets['js']['plugins']['src']);
        $this->assertTrue($assets['js']['plugins']['defer']);
        $this->assertArrayNotHasKey('type', $assets['js']['plugins']);

        $this->assertSame($base . '/js/index.js', $assets['js']['index']['src']);
        $this->assertSame('module', $assets['js']['index']['type']);
    }

    /**
     * @covers ::assets
     */
    public function testAssetsDev(): void
    {
        // dev mode
        $app = $this->app->clone([
            'request' => [
                'url' => 'http://sandbox.test'
            ],
            'options' => [
                'panel' => [
                    'dev' => true
                ]
            ]
        ]);

        // add vite file
        F::write($app->roots()->panel() . '/.vite-running', '');

        $assets = Document::assets($app);
        $base   = 'http://sandbox.test:3000';

        // css
        $this->assertSame(['plugins' => '/media/plugins/index.css?0'], $assets['css']);

        // icons
        $this->assertSame($base . '/apple-touch-icon.png', $assets['icons']['apple-touch-icon']['url']);
        $this->assertSame($base . '/favicon.svg', $assets['icons']['shortcut icon']['url']);
        $this->assertSame($base . '/favicon.png', $assets['icons']['alternate icon']['url']);

        // js
        $this->assertSame([
            'pluginloader' => $base . '/js/plugins.js',
            'plugins' => '/media/plugins/index.js?0',
            'index' => $base . '/src/index.js',
            'vite' => $base . '/@vite/client'
        ], array_map(fn ($js) => $js['src'], $assets['js']));
    }

    /**
     * @covers ::assets
     */
    public function testAssetsCustomUrl(): void
    {
        // dev mode with custom url
        $app = $this->app->clone([
            'request' => [
                'url' => 'http://sandbox.test'
            ],
            'options' => [
                'panel' => [
                    'dev' => 'http://localhost:3000'
                ]
            ]
        ]);

        $assets = Document::assets($app);
        $base   = 'http://localhost:3000';

        // css
        $this->assertSame(['plugins' => '/media/plugins/index.css?0'], $assets['css']);

        // icons
        $this->assertSame($base . '/apple-touch-icon.png', $assets['icons']['apple-touch-icon']['url']);
        $this->assertSame($base . '/favicon.svg', $assets['icons']['shortcut icon']['url']);
        $this->assertSame($base . '/favicon.png', $assets['icons']['alternate icon']['url']);

        // js
        $this->assertSame([
            'pluginloader' => $base . '/js/plugins.js',
            'plugins' => '/media/plugins/index.js?0',
            'index' => $base . '/src/index.js',
            'vite' => $base . '/@vite/client'
        ], array_map(fn ($js) => $js['src'], $assets['js']));
    }

    /**
     * @covers ::assets
     */
    public function testAssetsCustomCssJs(): void
    {
        // custom panel css and js
        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'css' => '/assets/panel.css',
                    'js'  => '/assets/panel.js',
                ]
            ]
        ]);

        // create dummy assets
        F::write($this->tmp . '/assets/panel.css', 'test');
        F::write($this->tmp . '/assets/panel.js', 'test');

        $assets = Document::assets($app);

        $this->assertTrue(Str::contains($assets['css']['custom'], 'assets/panel.css'));
        $this->assertTrue(Str::contains($assets['js']['custom']['src'], 'assets/panel.js'));

        // clean up vite file
        F::remove($app->roots()->panel() . '/.vite-running');
    }

    /**
     * @covers ::customAsset
     */
    public function testCustomCss(): void
    {
        // invalid
        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'css' => 'nonexists.css'
                ]
            ]
        ]);

        $this->assertNull(Document::customAsset('panel.css'));

        // valid
        F::write($this->tmp . '/panel.css', '');

        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'css' => 'panel.css'
                ]
            ]
        ]);

        $this->assertStringContainsString(
            '/panel.css',
            Document::customAsset('panel.css')
        );
    }

    /**
     * @covers ::customAsset
     */
    public function testCustomJs(): void
    {
        // invalid
        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'js' => 'nonexists.js'
                ]
            ]
        ]);

        $this->assertNull(Document::customAsset('panel.js'));

        // valid
        F::write($this->tmp . '/panel.js', '');

        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'js' => 'panel.js'
                ]
            ]
        ]);

        $this->assertStringContainsString(
            '/panel.js',
            Document::customAsset('panel.js')
        );
    }

    /**
     * @covers ::favicon
     */
    public function testFaviconArray(): void
    {
        // array
        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'favicon' => [
                        'shortcut icon' => [
                            'type' => 'image/svg+xml',
                            'url'  => 'assets/my-favicon.svg',
                        ],
                        'alternate icon' => [
                            'type' => 'image/png',
                            'url'  => 'assets/my-favicon.png',
                        ]
                    ]
                ]
            ]
        ]);

        $icons = Document::favicon();
        $this->assertSame('assets/my-favicon.svg', $icons['shortcut icon']['url']);
        $this->assertSame('assets/my-favicon.png', $icons['alternate icon']['url']);
    }

    /**
     * @covers ::favicon
     */
    public function testFaviconString(): void
    {
        // single string
        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'favicon' => 'assets/favicon.ico'
                ]
            ]
        ]);

        $icons = Document::favicon();
        $this->assertSame('image/x-icon', $icons['shortcut icon']['type']);
        $this->assertSame('assets/favicon.ico', $icons['shortcut icon']['url']);
    }

    /**
     * @covers ::favicon
     */
    public function testFaviconInvalid(): void
    {
        // single string
        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'favicon' => 5
                ]
            ]
        ]);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid panel.favicon option');
        $icons = Document::favicon();
    }

    /**
     * @covers ::icons
     */
    public function testIcons(): void
    {
        $icons = Document::icons();

        $this->assertNotNull($icons);
        $this->assertTrue(strpos($icons, '<svg', 0) !== false);
    }

    /**
     * @covers ::link
     */
    public function testLink(): void
    {
        // create links
        $link = Document::link($this->app);
        $this->assertTrue($link);

        // try again to create links, should be return false
        $link = Document::link($this->app);
        $this->assertFalse($link);
    }

    /**
     * @covers ::response
     */
    public function testResponse(): void
    {
        // create panel dist files first to avoid redirect
        Document::link($this->app);

        // get panel response
        $response = Document::response([
            'test' => 'Test'
        ]);

        $this->assertInstanceOf('\Kirby\Http\Response', $response);
        $this->assertSame(200, $response->code());
        $this->assertSame('text/html', $response->type());
        $this->assertSame('UTF-8', $response->charset());
        $this->assertNotNull($response->body());
    }
}
