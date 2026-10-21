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
	protected string $devCssA;
	protected string $devCssB;
	protected string $devCssC;
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
		$this->devCssA = static::TMP . '/site/plugins/a/index.dev.css';
		$this->devJsA  = static::TMP . '/site/plugins/a/index.dev.js';

		F::write(static::TMP . '/site/plugins/b/index.php', '<?php Kirby::plugin("test/b", []);');
		F::write($this->cssB = static::TMP . '/site/plugins/b/index.css', 'b');
		F::write($this->jsB = static::TMP . '/site/plugins/b/index.js', 'b');
		$this->devCssB = static::TMP . '/site/plugins/b/index.dev.css';
		$this->devJsB  = static::TMP . '/site/plugins/b/index.dev.js';

		F::write(static::TMP . '/site/plugins/c/index.php', '<?php Kirby::plugin("test/c", []);');
		F::write($this->cssC = static::TMP . '/site/plugins/c/index.css', 'c');
		F::write($this->jsC = static::TMP . '/site/plugins/c/index.js', 'c');
		$this->devCssC = static::TMP . '/site/plugins/c/index.dev.css';
		$this->devJsC  = static::TMP . '/site/plugins/c/index.dev.js';
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
			$media . 'a.' . hash('xxh3', 'a') . '.css',
			$media . 'c.' . hash('xxh3', 'c') . '.css',
		], $plugins->css());
	}

	public function testCssDuringDevelopment(): void
	{
		$this->createPlugins();

		// a plugin in development with its own stylesheet
		F::write($this->devJsB, 'dev b');
		F::write($this->devCssB, 'dev b');

		// a plugin in development whose styles the dev server injects
		F::write($this->devJsC, 'dev c');

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$plugins = new Plugins();
		$media   = $this->app->url('media') . '/plugins/test/';

		$this->assertSame([
			$media . 'a.' . hash('xxh3', 'a') . '.css',
			$media . 'b.' . hash('xxh3', 'dev b') . '.dev.css',
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
			$media . 'a.' . hash('xxh3', 'a') . '.js',
			$media . 'b.' . hash('xxh3', 'dev b') . '.dev.js',
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
		F::write($this->devCssA, 'dev a');
		F::write($this->devJsA, 'dev a');

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$response = Plugins::resolve('test/a', hash('xxh3', 'a'), 'js');
		$this->assertSame('a', $response->body());
		$this->assertSame('text/javascript', $response->type());
		$this->assertSame(
			'public, max-age=31536000, immutable',
			$response->headers()['Cache-Control']
		);

		$response = Plugins::resolve('test/a', hash('xxh3', 'dev a'), 'dev.js');
		$this->assertSame('dev a', $response->body());

		$response = Plugins::resolve('test/a', hash('xxh3', 'a'), 'css');
		$this->assertSame('a', $response->body());
		$this->assertSame('text/css', $response->type());

		$response = Plugins::resolve('test/a', hash('xxh3', 'dev a'), 'dev.css');
		$this->assertSame('dev a', $response->body());
		$this->assertSame('text/css', $response->type());
	}

	public function testResolveRoute(): void
	{
		$this->createPlugins();

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$hash = hash('xxh3', 'b');

		$response = $app->call('media/plugins/test/b.' . $hash . '.js');
		$this->assertSame('b', $response->body());

		$response = $app->call('media/plugins/test/b.' . $hash . '.css');
		$this->assertSame('b', $response->body());

		F::write($this->devCssB, 'dev b');
		$response = $app->call('media/plugins/test/b.' . hash('xxh3', 'dev b') . '.dev.css');
		$this->assertSame('dev b', $response->body());

		$this->assertNull($app->call('media/plugins/test/b.' . $hash . '.dev.js'));
		$this->assertNull($app->call('media/plugins/test/b.js'));
		$this->assertNull($app->call('media/plugins/index.css'));
	}

	public function testResolveWithOutdatedHash(): void
	{
		$this->createPlugins();

		// app must be created again to load the new plugins
		$app  = $this->app->clone();
		$hash = hash('xxh3', 'a');

		// the plugin gets updated
		F::write($this->jsA, 'a2');

		$this->assertNull(Plugins::resolve('test/a', $hash, 'js'));
		$this->assertNull($app->call('media/plugins/test/a.' . $hash . '.js'));
	}

	public function testResolveWithoutFile(): void
	{
		$this->createPlugins();

		// app must be created again to load the new plugins
		$app = $this->app->clone();

		$this->assertNull(Plugins::resolve('test/a', hash('xxh3', ''), 'dev.js'));
	}

	public function testResolveWithoutPlugin(): void
	{
		$this->assertNull(Plugins::resolve('test/missing', hash('xxh3', ''), 'js'));
	}
}
