<?php

namespace Kirby\Panel;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Plugins::class)]
class PluginsTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Plugins';

	protected string $cssA;
	protected string $cssB;
	protected string $cssC;
	protected string $jsA;
	protected string $jsB;
	protected string $jsC;

	public function createPlugins()
	{
		$time = \time() + 2;

		F::write(static::TMP . '/site/plugins/a/index.php', '<?php Kirby::plugin("test/a", []);');
		touch(static::TMP . '/site/plugins/a/index.php', $time);
		F::write($this->cssA = static::TMP . '/site/plugins/a/index.css', 'a');
		touch($this->cssA, $time);
		F::write($this->jsA = static::TMP . '/site/plugins/a/index.js', 'a');
		touch($this->jsA, $time);

		F::write(static::TMP . '/site/plugins/b/index.php', '<?php Kirby::plugin("test/b", []);');
		touch(static::TMP . '/site/plugins/b/index.php', $time);
		F::write($this->cssB = static::TMP . '/site/plugins/b/index.css', 'b');
		touch($this->cssB, $time);
		F::write($this->jsB = static::TMP . '/site/plugins/b/index.js', 'b');
		touch($this->jsB, $time);

		F::write(static::TMP . '/site/plugins/c/index.php', '<?php Kirby::plugin("test/c", []);');
		touch(static::TMP . '/site/plugins/c/index.php', $time);
		F::write($this->cssC = static::TMP . '/site/plugins/c/index.css', 'c');
		touch($this->cssC, $time);
		F::write($this->jsC = static::TMP . '/site/plugins/c/index.js', 'c');
		touch($this->jsC, $time);

		return $time;
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testFiles(): void
	{
		$this->createPlugins();

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$plugins  = new Plugins();
		$expected = [
			$this->cssA,
			$this->jsA,
			$this->cssB,
			$this->jsB,
			$this->cssC,
			$this->jsC,
		];

		$this->assertSame($expected, $plugins->files());
		// from cached property
		$this->assertSame($expected, $plugins->files());
	}

	public function testModifiedWithoutFiles(): void
	{
		$plugins = new Plugins();
		$this->assertSame(0, $plugins->modified());
	}

	public function testModifiedWithFiles(): void
	{
		$time = $this->createPlugins();

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$plugins = new Plugins();
		$this->assertSame($time, $plugins->modified());
	}

	public function testRead(): void
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
	}

	public function testUrl(): void
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
