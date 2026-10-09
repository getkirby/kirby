<?php

namespace Kirby\Panel;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Plugins::class)]
class PluginsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Plugins';

	protected string $cssA;
	protected string $cssB;
	protected string $cssC;
	protected string $jsA;
	protected string $jsB;
	protected string $jsC;
	protected string $devJsA;
	protected string $devJsB;
	protected string $devJsC;

	public function createPlugins()
	{
		$time = \time() + 2;

		F::write(static::TMP . '/site/plugins/a/index.php', '<?php Kirby::plugin("test/a", []);');
		touch(static::TMP . '/site/plugins/a/index.php', $time);
		F::write($this->cssA = static::TMP . '/site/plugins/a/index.css', 'a');
		touch($this->cssA, $time);
		F::write($this->jsA = static::TMP . '/site/plugins/a/index.js', 'a');
		touch($this->jsA, $time);
		F::write($this->jsA = static::TMP . '/site/plugins/a/index.js', 'a');
		$this->devJsA = static::TMP . '/site/plugins/a/index.dev.js';

		F::write(static::TMP . '/site/plugins/b/index.php', '<?php Kirby::plugin("test/b", []);');
		touch(static::TMP . '/site/plugins/b/index.php', $time);
		F::write($this->cssB = static::TMP . '/site/plugins/b/index.css', 'b');
		touch($this->cssB, $time);
		F::write($this->jsB = static::TMP . '/site/plugins/b/index.js', 'b');
		touch($this->jsB, $time);
		$this->devJsB = static::TMP . '/site/plugins/b/index.dev.js';

		F::write(static::TMP . '/site/plugins/c/index.php', '<?php Kirby::plugin("test/c", []);');
		touch(static::TMP . '/site/plugins/c/index.php', $time);
		F::write($this->cssC = static::TMP . '/site/plugins/c/index.css', 'c');
		touch($this->cssC, $time);
		F::write($this->jsC = static::TMP . '/site/plugins/c/index.js', 'c');
		touch($this->jsC, $time);
		$this->devJsC = static::TMP . '/site/plugins/c/index.dev.js';

		return $time;
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testCss(): void
	{
		$time = $this->createPlugins();

		// an empty stylesheet
		F::write($this->cssB, '');

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$plugins = new Plugins();
		$media   = $this->app->url('media') . '/plugins/test/';

		$this->assertSame([
			$media . 'a.css?' . $time,
			$media . 'c.css?' . $time,
		], $plugins->css());
	}

	public function testCssWithoutPlugins(): void
	{
		$plugins = new Plugins();
		$this->assertSame([], $plugins->css());
	}

	public function testJs(): void
	{
		$time = $this->createPlugins();

		// a plugin in development
		F::write($this->devJsB, 'dev b');
		touch($this->devJsB, $time + 1);

		// an empty script
		F::write($this->jsC, '');

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$plugins = new Plugins();
		$media   = $this->app->url('media') . '/plugins/test/';

		$this->assertSame([
			$media . 'a.js?' . F::modified($this->jsA),
			$media . 'b.dev.js?' . ($time + 1),
		], $plugins->js());
	}

	public function testResolve(): void
	{
		$this->createPlugins();
		F::write($this->devJsA, 'dev a');

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$response = Plugins::resolve('test/a', 'js');
		$this->assertSame('a', $response->body());
		$this->assertSame('text/javascript', $response->type());
		$this->assertSame(
			'public, max-age=31536000, immutable',
			$response->headers()['Cache-Control']
		);

		$response = Plugins::resolve('test/a', 'dev.js');
		$this->assertSame('dev a', $response->body());

		$response = Plugins::resolve('test/a', 'css');
		$this->assertSame('a', $response->body());
		$this->assertSame('text/css', $response->type());
	}

	public function testResolveRoute(): void
	{
		$this->createPlugins();

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$response = $app->call('media/plugins/test/b.js');
		$this->assertSame('b', $response->body());

		$response = $app->call('media/plugins/test/b.css');
		$this->assertSame('b', $response->body());

		$this->assertNull($app->call('media/plugins/test/b.dev.js'));
		$this->assertNull($app->call('media/plugins/index.css'));
	}

	public function testResolveWithoutFile(): void
	{
		$this->createPlugins();

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$this->assertNull(Plugins::resolve('test/a', 'dev.js'));
	}

	public function testResolveWithoutPlugin(): void
	{
		$this->assertNull(Plugins::resolve('test/missing', 'js'));
	}
}
