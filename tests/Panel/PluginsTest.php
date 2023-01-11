<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Plugins
 */
class PluginsTest extends TestCase
{
	protected $app;
	protected $tmp = __DIR__ . '/tmp';
	protected $cssA;
	protected $cssB;
	protected $cssC;
	protected $jsA;
	protected $jsB;
	protected $jsC;
	protected $mjsA;
	protected $mjsB;
	protected $mjsC;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp
			]
		]);
	}

	public function createPlugins(bool $addDevMjs = false)
	{
		$time = \time() + 2;

		F::write($this->tmp . '/site/plugins/a/index.php', '<?php Kirby::plugin("test/a", []);');
		touch($this->tmp . '/site/plugins/a/index.php', $time);
		F::write($this->cssA = $this->tmp . '/site/plugins/a/index.css', 'a');
		touch($this->cssA, $time);
		F::write($this->jsA = $this->tmp . '/site/plugins/a/index.js', 'a');
		touch($this->jsA, $time);
		$this->mjsA = $this->tmp . '/site/plugins/a/index.dev.mjs';

		F::write($this->tmp . '/site/plugins/b/index.php', '<?php Kirby::plugin("test/b", []);');
		touch($this->tmp . '/site/plugins/b/index.php', $time);
		F::write($this->cssB = $this->tmp . '/site/plugins/b/index.css', 'b');
		touch($this->cssB, $time);
		F::write($this->jsB = $this->tmp . '/site/plugins/b/index.js', 'b');
		touch($this->jsB, $time);
		$this->mjsB = $this->tmp . '/site/plugins/b/index.dev.mjs';

		F::write($this->tmp . '/site/plugins/c/index.php', '<?php Kirby::plugin("test/c", []);');
		touch($this->tmp . '/site/plugins/c/index.php', $time);
		F::write($this->cssC = $this->tmp . '/site/plugins/c/index.css', 'c');
		touch($this->cssC, $time);
		F::write($this->jsC = $this->tmp . '/site/plugins/c/index.js', 'c');
		touch($this->jsC, $time);
		$this->mjsC = $this->tmp . '/site/plugins/c/index.dev.mjs';

		if ($addDevMjs === true) {
			F::write($this->mjsC, 'c');
			touch($this->mjsC, $time);
		}

		return $time;
	}

	public function tearDown(): void
	{
		Dir::remove($this->tmp);
	}

	/**
	 * @covers ::files
	 */
	public function testFiles()
	{
		$this->createPlugins();

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$plugins  = new Plugins();
		$expected = [
			$this->cssA,
			$this->jsA,
			$this->mjsA,
			$this->cssB,
			$this->jsB,
			$this->mjsB,
			$this->cssC,
			$this->jsC,
			$this->mjsC
		];

		$this->assertSame($expected, $plugins->files());
		// from cached property
		$this->assertSame($expected, $plugins->files());
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedWithoutFiles()
	{
		$plugins = new Plugins();
		$this->assertSame(0, $plugins->modified());
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedWithFiles()
	{
		$time = $this->createPlugins();

		$plugins = new Plugins();
		$this->assertSame($time, $plugins->modified());
	}

	/**
	 * @covers ::read
	 */
	public function testRead()
	{
		$this->createPlugins();

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$plugins = new Plugins();

		// css
		$expected = "a\n\nb\n\nc";
		$this->assertSame($expected, $plugins->read('css'));

		// js
		$expected = "a;\n\nb;\n\nc;";
		$this->assertSame($expected, $plugins->read('js'));

		// mjs - must be completely empty and not include the loader code
		$expected = '';
		$this->assertSame($expected, $plugins->read('mjs'));
	}

	/**
	 * @covers ::read
	 */
	public function testReadWithDevMjs()
	{
		$this->createPlugins(true);

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$plugins = new Plugins();

		// css
		$expected = "a\n\nb\n\nc";
		$this->assertSame($expected, $plugins->read('css'));

		// js - shouldn't include c because c has an index.dev.mjs
		$expected = "a;\n\nb;";
		$this->assertSame($expected, $plugins->read('js'));

		// mjs - c as base64 data uri wrapped in the loader code
		$expected = 'try { await Promise.all(["data:text/javascript;base64,Yw=="].map(url => import(url))) } catch (e) { console.error(e) }' . PHP_EOL;
		$this->assertSame($expected, $plugins->read('mjs'));
	}

	/**
	 * @covers ::url
	 */
	public function testUrl()
	{
		// css
		$plugins  = new Plugins();
		$expected = $this->app->url('media') . '/plugins/index.css?0';

		$this->assertSame($expected, $plugins->url('css'));

		// js
		$plugins  = new Plugins();
		$expected = $this->app->url('media') . '/plugins/index.js?0';

		$this->assertSame($expected, $plugins->url('js'));
	}
}
