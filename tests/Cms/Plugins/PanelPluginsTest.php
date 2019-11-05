<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class PanelPluginsTest extends TestCase
{
    protected $app;
    protected $fixtures;
    protected $cssA;
    protected $cssB;
    protected $jsA;
    protected $jsB;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/PanelPluginsTest'
            ]
        ]);
    }

    public function createPlugins()
    {
        F::write($this->fixtures . '/site/plugins/a/index.php', '<?php Kirby::plugin("test/a", []);');
        F::write($this->cssA = $this->fixtures . '/site/plugins/a/index.css', 'a');
        F::write($this->jsA  = $this->fixtures . '/site/plugins/a/index.js', 'a');

        F::write($this->fixtures . '/site/plugins/b/index.php', '<?php Kirby::plugin("test/b", []);');
        F::write($this->cssB = $this->fixtures . '/site/plugins/b/index.css', 'b');
        F::write($this->jsB  = $this->fixtures . '/site/plugins/b/index.js', 'b');
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testFiles()
    {
        $this->createPlugins();

        // app must be created again to load the new plugins
        $app = $this->app->clone();

        $plugins  = new PanelPlugins();
        $expected = [$this->cssA, $this->jsA, $this->cssB, $this->jsB];

        $this->assertEquals($expected, $plugins->files());
    }

    public function testModifiedWithoutFiles()
    {
        $plugins = new PanelPlugins();
        $this->assertEquals(0, $plugins->modified());
    }

    public function testModifiedWithFiles()
    {
        $this->createPlugins();

        $time = \time();

        $plugins = new PanelPlugins();
        $this->assertEquals($time, $plugins->modified());
    }

    public function testRead()
    {
        $this->createPlugins();

        // app must be created again to load the new plugins
        $app = $this->app->clone();

        $plugins = new PanelPlugins();

        // css
        $expected = "a\n\nb";
        $this->assertEquals($expected, $plugins->read('css'));

        // js
        $expected = "a;\n\nb;";
        $this->assertEquals($expected, $plugins->read('js'));
    }

    public function testUrl()
    {
        // css
        $plugins  = new PanelPlugins();
        $expected = $this->app->url('media') . '/plugins/index.css?0';

        $this->assertEquals($expected, $plugins->url('css'));

        // js
        $plugins  = new PanelPlugins();
        $expected = $this->app->url('media') . '/plugins/index.js?0';

        $this->assertEquals($expected, $plugins->url('js'));
    }
}
