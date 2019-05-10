<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class PanelPluginsTest extends TestCase
{
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

        // css
        $plugins  = new PanelPlugins('css');
        $expected = [$this->cssA, $this->cssB];

        $this->assertEquals($expected, $plugins->files());

        // js
        $plugins  = new PanelPlugins('js');
        $expected = [$this->jsA, $this->jsB];

        $this->assertEquals($expected, $plugins->files());
    }

    public function testFolder()
    {
        $plugins = new PanelPlugins('css');
        $this->assertEquals('panel/' . $this->app->versionHash() . '/plugins/css', $plugins->folder());

        $plugins = new PanelPlugins('js');
        $this->assertEquals('panel/' . $this->app->versionHash() . '/plugins/js', $plugins->folder());
    }

    public function testHashWithoutFiles()
    {
        $plugins = new PanelPlugins('css');
        $this->assertEquals('00000000-0', $plugins->hash());

        $plugins = new PanelPlugins('js');
        $this->assertEquals('00000000-0', $plugins->hash());
    }

    public function testHashWithFiles()
    {
        $this->createPlugins();

        $plugins = new PanelPlugins('css');
        $this->assertEquals($plugins->id() . '-' . $plugins->modified(), $plugins->hash());

        $plugins = new PanelPlugins('js');
        $this->assertEquals($plugins->id() . '-' . $plugins->modified(), $plugins->hash());
    }

    public function testIdWithoutFiles()
    {
        $plugins = new PanelPlugins('css');
        $this->assertEquals(0, $plugins->id());

        $plugins = new PanelPlugins('js');
        $this->assertEquals(0, $plugins->id());
    }

    public function testIdWithFiles()
    {
        $this->createPlugins();

        $plugins = new PanelPlugins('css');
        $this->assertRegExp('![a-z0-9]{8}!', $plugins->id());

        $plugins = new PanelPlugins('js');
        $this->assertRegExp('![a-z0-9]{8}!', $plugins->id());
    }

    public function testModifiedWithoutFiles()
    {
        $plugins = new PanelPlugins('css');
        $this->assertEquals(0, $plugins->modified());

        $plugins = new PanelPlugins('js');
        $this->assertEquals(0, $plugins->modified());
    }

    public function testModifiedWithFiles()
    {
        $this->createPlugins();

        $time = time();

        $plugins = new PanelPlugins('css');
        $this->assertEquals($time, $plugins->modified());

        $plugins = new PanelPlugins('js');
        $this->assertEquals($time, $plugins->modified());
    }

    public function testPath()
    {
        $plugins = new PanelPlugins('css');
        $this->assertEquals($plugins->folder() . '/' . $plugins->hash() . '/index.css', $plugins->path());

        $plugins = new PanelPlugins('js');
        $this->assertEquals($plugins->folder() . '/' . $plugins->hash() . '/index.js', $plugins->path());
    }

    public function testPublish()
    {
        $plugins = new PanelPlugins('css');

        $this->assertFalse($plugins->exist());
        $this->assertTrue($plugins->publish());
        $this->assertTrue($plugins->exist());
    }

    public function testRead()
    {
        $this->createPlugins();

        // app must be created again to load the new plugins
        $app = $this->app->clone();

        // css
        $plugins  = new PanelPlugins('css');
        $expected = "a\nb";

        $this->assertEquals($expected, $plugins->read());

        // js
        $plugins  = new PanelPlugins('js');
        $expected = "a\nb";

        $this->assertEquals($expected, $plugins->read());
    }

    public function testRoot()
    {
        // css
        $plugins  = new PanelPlugins('css');
        $expected = $this->app->root('media') . '/panel/' . $this->app->versionHash() . '/plugins/css/00000000-0/index.css';

        $this->assertEquals($expected, $plugins->root());

        // js
        $plugins  = new PanelPlugins('js');
        $expected = $this->app->root('media') . '/panel/' . $this->app->versionHash() . '/plugins/js/00000000-0/index.js';

        $this->assertEquals($expected, $plugins->root());
    }

    public function testUrl()
    {
        // css
        $plugins  = new PanelPlugins('css');
        $expected = $this->app->url('media') . '/panel/' . $this->app->versionHash() . '/plugins/css/00000000-0/index.css';

        $this->assertEquals($expected, $plugins->url());

        // js
        $plugins  = new PanelPlugins('js');
        $expected = $this->app->url('media') . '/panel/' . $this->app->versionHash() . '/plugins/js/00000000-0/index.js';

        $this->assertEquals($expected, $plugins->url());
    }

    public function testWrite()
    {
        $plugins = new PanelPlugins('css');

        $this->assertFileNotExists($plugins->root());
        $this->assertTrue($plugins->write());
        $this->assertFileExists($plugins->root());
    }
}
