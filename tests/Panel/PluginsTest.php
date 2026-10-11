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

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function createPlugins(): void
	{
		F::write(static::TMP . '/site/plugins/a/index.php', '<?php Kirby::plugin("test/a", []);');
		F::write($this->cssA = static::TMP . '/site/plugins/a/index.css', 'a');
		F::write($this->jsA = static::TMP . '/site/plugins/a/index.js', 'a');
		$this->devJsA = static::TMP . '/site/plugins/a/index.dev.js';

		F::write(static::TMP . '/site/plugins/b/index.php', '<?php Kirby::plugin("test/b", []);');
		F::write($this->cssB = static::TMP . '/site/plugins/b/index.css', 'b');
		F::write($this->jsB = static::TMP . '/site/plugins/b/index.js', 'b');
		$this->devJsB = static::TMP . '/site/plugins/b/index.dev.js';

		F::write(static::TMP . '/site/plugins/c/index.php', '<?php Kirby::plugin("test/c", []);');
		F::write($this->cssC = static::TMP . '/site/plugins/c/index.css', 'c');
		F::write($this->jsC = static::TMP . '/site/plugins/c/index.js', 'c');
		$this->devJsC = static::TMP . '/site/plugins/c/index.dev.js';
	}

	public function testCss(): void
	{
		$this->createPlugins();

		// an empty stylesheet
		F::write($this->cssB, '');

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$plugins = new Plugins();
		$media   = $this->app->url('media') . '/plugins/test/';

		$this->assertSame([
			$media . 'a.css?' . hash('xxh3', 'a'),
			$media . 'c.css?' . hash('xxh3', 'c'),
		], $plugins->css());
	}

	public function testCssWithoutPlugins(): void
	{
		$plugins = new Plugins();
		$this->assertSame([], $plugins->css());
	}

	public function testJs(): void
	{
		$this->createPlugins();

		// a plugin in development
		F::write($this->devJsB, 'dev b');

		// an empty script
		F::write($this->jsC, '');

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$plugins = new Plugins();
		$media   = $this->app->url('media') . '/plugins/test/';

		$this->assertSame([
			$media . 'a.js?' . hash('xxh3', 'a'),
			$media . 'b.dev.js?' . hash('xxh3', 'dev b'),
		], $plugins->js());
	}

	public function testJsWithSameModificationTime(): void
	{
		$this->createPlugins();
		touch($this->jsA, $time = time());

		// app must be created again to load the new plugins
		$app    = $this->app->clone();
		$before = (new Plugins())->js();

		// a rebuild within the same second
		F::write($this->jsA, 'a2');
		touch($this->jsA, $time);
		clearstatcache();

		$this->assertNotSame($before, (new Plugins())->js());
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
