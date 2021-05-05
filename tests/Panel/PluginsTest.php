<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class PluginsTest extends TestCase
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
                'index' => $this->fixtures = __DIR__ . '/fixtures/PluginsTest'
            ]
        ]);
    }

    public function createPlugins()
    {
        $time = \time() + 2;

        F::write($this->fixtures . '/site/plugins/a/index.php', '<?php Kirby::plugin("test/a", []);');
        touch($this->fixtures . '/site/plugins/a/index.php', $time);
        F::write($this->cssA = $this->fixtures . '/site/plugins/a/index.css', 'a');
        touch($this->cssA, $time);
        F::write($this->jsA = $this->fixtures . '/site/plugins/a/index.js', 'a');
        touch($this->jsA, $time);

        F::write($this->fixtures . '/site/plugins/b/index.php', '<?php Kirby::plugin("test/b", []);');
        touch($this->fixtures . '/site/plugins/b/index.php', $time);
        F::write($this->cssB = $this->fixtures . '/site/plugins/b/index.css', 'b');
        touch($this->cssB, $time);
        F::write($this->jsB = $this->fixtures . '/site/plugins/b/index.js', 'b');
        touch($this->jsB, $time);

        return $time;
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

        $plugins  = new Plugins();
        $expected = [$this->cssA, $this->jsA, $this->cssB, $this->jsB];

        $this->assertEquals($expected, $plugins->files());
    }

    public function testModifiedWithoutFiles()
    {
        $plugins = new Plugins();
        $this->assertEquals(0, $plugins->modified());
    }

    public function testModifiedWithFiles()
    {
        $time = $this->createPlugins();

        $plugins = new Plugins();
        $this->assertEquals($time, $plugins->modified());
    }

    public function testRead()
    {
        $this->createPlugins();

        // app must be created again to load the new plugins
        $app = $this->app->clone();

        $plugins = new Plugins();

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
        $plugins  = new Plugins();
        $expected = $this->app->url('media') . '/plugins/index.css?0';

        $this->assertEquals($expected, $plugins->url('css'));

        // js
        $plugins  = new Plugins();
        $expected = $this->app->url('media') . '/plugins/index.js?0';

        $this->assertEquals($expected, $plugins->url('js'));
    }
}
