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
    public function testAssets(): void
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


        // dev mode
        $this->app = $this->app->clone([
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
        F::write($viteFile = $this->app->roots()->panel() . '/.vite-running', '');

        $assets = Document::assets($this->app);
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


        // dev mode with custom url
        $this->app = $this->app->clone([
            'request' => [
                'url' => 'http://sandbox.test'
            ],
            'options' => [
                'panel' => [
                    'dev' => 'http://localhost:3000'
                ]
            ]
        ]);

        $assets = Document::assets($this->app);
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


        // custom panel css and js
        $this->app = $this->app->clone([
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

        $assets = Document::assets($this->app);

        $this->assertTrue(Str::contains($assets['css']['custom'], 'assets/panel.css'));
        $this->assertTrue(Str::contains($assets['js']['custom']['src'], 'assets/panel.js'));

        // clean up vite file
        F::remove($viteFile);
    }

    /**
     * @covers ::customCss
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

        $this->assertNull(Document::customCss());

        // valid
        F::write($this->tmp . '/panel.css', '');

        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'css' => 'panel.css'
                ]
            ]
        ]);

        $this->assertTrue(Str::contains(Document::customCss(), '/panel.css'));
    }

    /**
     * @covers ::customJs
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

        $this->assertNull(Document::customJs());

        // valid
        F::write($this->tmp . '/panel.js', '');

        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'js' => 'panel.js'
                ]
            ]
        ]);

        $this->assertTrue(Str::contains(Document::customJs(), '/panel.js'));
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
