<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\TestCase;

class PluginAssetsTest extends TestCase
{
	protected $app;
	protected $tmp = __DIR__ . '/tmp/PluginAssetsTest';

	public function setUp(): void
	{
		$a = $this->tmp . '/site/plugins/a';
		F::write($a . '/index.php', '<?php Kirby::plugin("getkirby/a", []);');
		F::write($a . '/assets/test.css', 'test');

		$b = $this->tmp . '/site/plugins/b';
		F::write($b . '/index.php', '<?php Kirby::plugin("getkirby/b", ["assets" => ["foo/bar.css"]]);' );
		F::write($b . '/foo/bar.css', 'test');

		$this->app = new App([
			'roots' => [
				'index' => $this->tmp
			]
		]);
	}

	public function tearDown(): void
	{
		Dir::remove($this->tmp);
	}

	public function testClean()
	{
		// create orphans
		F::write(
			$a = $this->tmp . '/media/plugins/getkirby/a/orphan.css',
			'test'
		);
		F::write(
			$b= $this->tmp . '/media/plugins/getkirby/a/assets/orphan.css',
			'test'
		);

		$this->assertFileExists($a);
		$this->assertFileExists($b);

		PluginAssets::clean('getkirby/a');

		$this->assertFileDoesNotExist($a);
		$this->assertFileDoesNotExist($b);
	}

	public function testResolve()
	{
		$response = PluginAssets::resolve('getkirby/b', 'foo/bar.css');
		$media    = $this->tmp . '/media/plugins/getkirby/b/foo/bar.css';

		$this->assertTrue(is_link($media));
		$this->assertSame(200, $response->code());
		$this->assertSame('text/css', $response->type());

		$response = PluginAssets::resolve('getkirby/b', 'assets/foo.css');
		$media    = $this->tmp . '/media/plugins/getkirby/b/assets/foo.css';
		$this->assertNull($response);
		$this->assertFalse(is_link($media));
	}

	public function testResolveAutomaticFromAssetsFolder()
	{
		$response = PluginAssets::resolve('getkirby/a', 'assets/test.css');
		$media    = $this->tmp . '/media/plugins/getkirby/a/assets/test.css';

		$this->assertTrue(is_link($media));
		$this->assertSame(200, $response->code());
		$this->assertSame('text/css', $response->type());

		$response = PluginAssets::resolve('getkirby/a', 'assets/foo.css');
		$media    = $this->tmp . '/media/plugins/getkirby/a/assets/foo.css';
		$this->assertNull($response);
		$this->assertFalse(is_link($media));
	}

	public function testCallPluginAssetInvalid()
	{
		$response = App::instance()->call('media/plugins/test/test/test.invalid');
		$this->assertNull($response);
	}
}
