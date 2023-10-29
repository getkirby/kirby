<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\PluginAssets
 */
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
		F::write($b . '/index.php', '<?php Kirby::plugin("getkirby/b", ["assets" => ["' . $b . '/foo/bar.css"]]);');
		F::write($b . '/foo/bar.css', 'test');

		$c = $this->tmp . '/site/plugins/c';
		F::write($c . '/index.php', '<?php Kirby::plugin("getkirby/c", ["assets" => ["test.css" => "' . $c . '/foo/bar.css"]]);');
		F::write($c . '/foo/bar.css', 'test');

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

	/**
	 * @covers ::clean
	 */
	public function testClean()
	{
		// create orphans
		F::write(
			$a = $this->tmp . '/media/plugins/getkirby/a/orphan.css',
			'test'
		);
		F::write(
			$b = $this->tmp . '/media/plugins/getkirby/a/assets/orphan.css',
			'test'
		);

		$this->assertFileExists($a);
		$this->assertFileExists($b);

		PluginAssets::clean('getkirby/a');

		$this->assertFileDoesNotExist($a);
		$this->assertFileDoesNotExist($b);
	}

	/**
	 * @covers ::css
	 * @covers ::js
	 */
	public function testCss()
	{
		// assets defined in plugin config
		$plugin = new Plugin('getkirby/test-plugin', [
			'root'   => $root = __DIR__ . '/tmp',
			'assets' => [
				'test.css' => $root . '/test.css',
				'test.js'  => $root . '/test.js'
			]
		]);

		$assets = PluginAssets::factory($plugin);
		$this->assertCount(2, $assets);
		$this->assertCount(1, $assets->css());
		$this->assertSame('test.css', $assets->css()->first()->path());
		$this->assertCount(1, $assets->js());
		$this->assertSame('test.js', $assets->js()->first()->path());
	}

	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		// assets defined in plugin config
		$plugin = new Plugin('getkirby/test-plugin', [
			'root'   => $root = __DIR__ . '/fixtures/plugin-assets',
			'assets' => [
				'c.css' => $root . '/a.css',
				'd.css' => $root . '/foo/b.css'
			]
		]);

		$assets = PluginAssets::factory($plugin);
		$this->assertInstanceOf(PluginAssets::class, $assets);
		$this->assertSame(2, $assets->count());
		$this->assertSame($root . '/a.css', $assets->get('c.css')->root());
		$this->assertSame($root . '/foo/b.css', $assets->get('d.css')->root());

		// assets defined as non-associative array in the plugin config
		$plugin = new Plugin('getkirby/test-plugin', [
			'root'   => $root,
			'assets' => [
				$root . '/a.css',
				$root . '/foo/b.css'
			]
		]);

		$assets = PluginAssets::factory($plugin);
		$this->assertInstanceOf(PluginAssets::class, $assets);
		$this->assertSame(2, $assets->count());
		$this->assertSame($root . '/a.css', $assets->get('a.css')->root());
		$this->assertSame($root . '/foo/b.css', $assets->get('foo/b.css')->root());

		// assets defined als closure in the plugin config
		$plugin = new Plugin('getkirby/test-plugin', [
			'root'   => __DIR__ . '/fixtures/plugin-assets',
			'assets' => fn () => [
				$root . '/a.css',
				$root . '/foo/b.css'
			]
		]);

		$assets = PluginAssets::factory($plugin);
		$this->assertInstanceOf(PluginAssets::class, $assets);
		$this->assertSame(2, $assets->count());
		$this->assertSame($root . '/a.css', $assets->get('a.css')->root());
		$this->assertSame($root . '/foo/b.css', $assets->get('foo/b.css')->root());

		// assets gathered from `assets` folder inside plugin root
		$plugin = new Plugin('getkirby/test-plugin', [
			'root' => __DIR__ . '/fixtures/plugin-assets'
		]);

		$assets = PluginAssets::factory($plugin);
		$this->assertInstanceOf(PluginAssets::class, $assets);
		$this->assertSame(1, $assets->count());
		$this->assertSame($root . '/assets/test.css', $assets->get('test.css')->root());
	}

	/**
	 * @covers ::plugin
	 */
	public function testPlugin()
	{
		$plugin = new Plugin('getkirby/test-plugin', [
			'root' => __DIR__ . '/fixtures/plugin-assets'
		]);

		$assets = PluginAssets::factory($plugin);
		$this->assertSame($plugin, $assets->plugin());
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve()
	{
		touch($this->tmp . '/site/plugins/b/foo/bar.css', 1337000000);

		// right path and hash
		$media    = $this->tmp . '/media/plugins/getkirby/b/110971429-1337000000/foo/bar.css';
		$response = PluginAssets::resolve(
			'getkirby/b',
			'110971429-1337000000',
			'foo/bar.css'
		);

		$this->assertTrue(is_link($media));
		$this->assertSame(200, $response->code());
		$this->assertSame('text/css', $response->type());

		// wrong path
		$media    = $this->tmp . '/media/plugins/getkirby/b/110971429-1337000000/assets/foo.css';
		$response = PluginAssets::resolve(
			'getkirby/b',
			'110971429-1337000000',
			'assets/foo.css'
		);

		$this->assertNull($response);
		$this->assertFalse(is_link($media));

		// wrong hash
		// TODO: remove when media hash is enforced as mandatory
		// $media    = $this->tmp . '/media/plugins/getkirby/b/110971429-1337000000/foo/bar.css';
		// $response = PluginAssets::resolve(
		// 	'getkirby/b',
		// 	'110971429-12345678',
		// 	'foo/bar.css'
		// );

		// $this->assertNull($response);
		// $this->assertFalse(is_link($media));

		// correct: different path and root
		touch($this->tmp . '/site/plugins/c/foo/bar.css', 1337000000);

		$media    = $this->tmp . '/media/plugins/getkirby/c/3526409702-1337000000/test.css';
		$response = PluginAssets::resolve(
			'getkirby/c',
			'3526409702-1337000000',
			'test.css'
		);

		$this->assertTrue(is_link($media));
		$this->assertSame(200, $response->code());
		$this->assertSame('text/css', $response->type());
	}

	public function testResolveAutomaticFromAssetsFolder()
	{
		touch($this->tmp . '/site/plugins/a/assets/test.css', 1337000000);

		$media    = $this->tmp . '/media/plugins/getkirby/a/3526409702-1337000000/test.css';
		$response = PluginAssets::resolve(
			'getkirby/a',
			'3526409702-1337000000',
			'test.css'
		);

		$this->assertTrue(is_link($media));
		$this->assertSame(200, $response->code());
		$this->assertSame('text/css', $response->type());


		$media    = $this->tmp . '/media/plugins/getkirby/a/3526409702-1337000000/assets/test.css';
		$response = PluginAssets::resolve(
			'getkirby/a',
			'3526409702-1337000000',
			'assets/test.css'
		);
		$this->assertNull($response);
		$this->assertFalse(is_link($media));
	}

	public function testAppCallInvalid()
	{
		$response = App::instance()->call('media/plugins/test/test/test.invalid');
		$this->assertNull($response);
	}
}
