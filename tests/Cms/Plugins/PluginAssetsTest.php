<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\TestCase;

class PluginAssetsTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->fixtures = __DIR__ . '/fixtures/PluginAssetsTest';

        Dir::remove($this->fixtures);

        $plugin = $this->fixtures . '/site/plugins/test';

        F::write($plugin . '/index.php', '<?php Kirby::plugin("test/test", []);');
        F::write($plugin . '/assets/test.css', 'test');

        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures
            ]
        ]);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testClean()
    {
        // create orphan
        F::write($orphan = $this->fixtures . '/media/plugins/test/test/orphan.css', 'test');

        $this->assertFileExists($orphan);

        PluginAssets::clean('test/test');

        $this->assertFileDoesNotExist($orphan);
    }

    public function testResolve()
    {
        $response = PluginAssets::resolve('test/test', 'test.css');

        $this->assertTrue(is_link($this->fixtures . '/media/plugins/test/test/test.css'));
        $this->assertEquals(200, $response->code());
        $this->assertEquals('text/css', $response->type());
    }
}
