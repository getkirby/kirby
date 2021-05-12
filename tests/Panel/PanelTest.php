<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class PanelTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/PanelTest',
            ]
        ]);

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

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

        $this->assertFalse(Panel::customCss($app));

        // valid
        F::write($this->fixtures . '/panel.css', '');

        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'css' => 'panel.css'
                ]
            ]
        ]);

        $this->assertTrue(strpos(Panel::customCss($app), '//panel.css', 0) !== false);
    }

    public function testIcons(): void
    {
        $icons = Panel::icons($this->app);

        $this->assertNotNull($icons);
        $this->assertTrue(strpos($icons, '<svg', 0) !== false);
    }

    public function testLink(): void
    {
        // create links
        $link = Panel::link($this->app);
        $this->assertTrue($link);

        // try again to create links, should be return false
        $link = Panel::link($this->app);
        $this->assertFalse($link);
    }

    public function testRender(): void
    {
        // create panel dist files first to avoid redirect
        Panel::link($this->app);

        // get panel response
        $response = Panel::render($this->app);
        $this->assertInstanceOf('\Kirby\Http\Response', $response);
        $this->assertSame(200, $response->code());
        $this->assertSame('text/html', $response->type());
        $this->assertSame('UTF-8', $response->charset());
        $this->assertNotNull($response->body());

        // clear session file
        $this->app->session()->destroy();
    }
}
