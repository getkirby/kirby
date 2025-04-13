<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use Kirby\Toolkit\Str;

/**
 * @coversDefaultClass \Kirby\Panel\Assets
 */
class AssetsTest extends TestCase
{
	public const TMP               = KIRBY_TMP_DIR . '/Panel.Assets';
	public const VITE_RUNNING_PATH = KIRBY_DIR . '/panel/.vite-running';

	protected App $app;
	protected bool $hadViteRunning;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		// initialize development mode to a known state
		$this->hadViteRunning = is_file(static::VITE_RUNNING_PATH);
		F::remove(static::VITE_RUNNING_PATH);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		// reset development mode
		if ($this->hadViteRunning === true) {
			touch(static::VITE_RUNNING_PATH);
		} else {
			F::remove(static::VITE_RUNNING_PATH);
		}

		Dir::remove(static::TMP);

		// clear fake json requests
		$_GET = [];

		// clean up $_SERVER
		unset($_SERVER['SERVER_SOFTWARE']);
	}

	public function setCustomUrl()
	{
		// dev mode with custom url
		return $this->app->clone([
			'request' => [
				'url' => 'http://sandbox.test'
			],
			'options' => [
				'panel' => [
					'dev' => 'http://localhost:3000'
				]
			]
		]);
	}

	public function setDevMode()
	{
		// dev mode
		$app = $this->app->clone([
			'request' => [
				'url' => 'http://sandbox.test'
			],
			'options' => [
				'panel' => [
					'dev' => true
				]
			]
		]);

		// add vite file
		touch(static::VITE_RUNNING_PATH);
	}

	/**
	 * @covers ::css
	 */
	public function testCss(): void
	{
		// default asset setup
		$assets  = new Assets();
		$base    = '/media/panel/' . $this->app->versionHash();
		$css     = $assets->css();

		// css
		$this->assertSame($base . '/css/style.min.css', $css['index']);
		$this->assertSame('/media/plugins/index.css?0', $css['plugins']);
	}

	/**
	 * @covers ::css
	 */
	public function testCssInDevMode(): void
	{
		$this->setDevMode();

		$assets = new Assets();
		$css    = $assets->css();

		// css
		$this->assertSame(['plugins' => '/media/plugins/index.css?0'], $css);
	}

	/**
	 * @covers ::css
	 * @covers ::custom
	 */
	public function testCssWithCustomFile(): void
	{
		touch(static::TMP . '/panel.css');
		touch(static::TMP . '/foo.css');

		// single
		$this->app->clone([
			'options' => [
				'panel' => [
					'css' => 'panel.css'
				]
			]
		]);

		$assets = new Assets();
		$assets = $assets->custom('panel.css');
		$this->assertStringContainsString('/panel.css', $assets['custom-0']);

		// multiple
		$this->app->clone([
			'options' => [
				'panel' => [
					'css' => ['panel.css', 'foo.css', $url = 'https://getkirby.com/bar.css']
				]
			]
		]);

		$assets = new Assets();
		$assets = $assets->custom('panel.css');
		$this->assertStringContainsString('/panel.css', $assets['custom-0']);
		$this->assertStringContainsString('/foo.css', $assets['custom-1']);
		$this->assertSame($url, $assets['custom-2']);
	}

	/**
	 * @covers ::css
	 */
	public function testCssWithCustomFileMissing(): void
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'css' => 'panel.css'
				]
			]
		]);

		$assets = new Assets();
		$this->assertEmpty($assets->custom('panel.css'));
	}

	/**
	 * @covers ::css
	 */
	public function testCssWithCustomUrl(): void
	{
		$this->setDevMode();
		$this->setCustomUrl();

		// default asset setup
		$assets = new Assets();
		$base   = 'http://localhost:3000';
		$css    = $assets->css();

		// css
		$this->assertSame(['plugins' => '/media/plugins/index.css?0'], $css);
	}

	/**
	 * @covers ::external
	 * @covers ::custom
	 */
	public function testExternalWithCustomCssJs(): void
	{
		// custom panel css and js
		$app = $this->app->clone([
			'options' => [
				'panel' => [
					'css' => '/assets/panel.css',
					'js'  => '/assets/panel.js',
				]
			]
		]);

		// create dummy assets
		F::write(static::TMP . '/assets/panel.css', 'test');
		F::write(static::TMP . '/assets/panel.js', 'test');

		$assets   = new Assets();
		$external = $assets->external();

		$this->assertTrue(Str::contains($external['css']['custom-0'], 'assets/panel.css'));
		$this->assertTrue(Str::contains($external['js']['custom-0']['src'], 'assets/panel.js'));
	}

	/**
	 * @covers ::favicons
	 */
	public function testFavicons(): void
	{
		// default asset setup
		$assets  = new Assets();
		$base    = '/media/panel/' . $this->app->versionHash();
		$favicons = $assets->favicons();

		// icons
		$this->assertSame($base . '/apple-touch-icon.png', $favicons[0]['href']);
		$this->assertSame($base . '/favicon.png', $favicons[1]['href']);
		$this->assertSame($base . '/favicon.svg', $favicons[2]['href']);
		$this->assertSame($base . '/apple-touch-icon-dark.png', $favicons[3]['href']);
		$this->assertSame($base . '/favicon-dark.png', $favicons[4]['href']);
	}

	/**
	 * @covers ::favicons
	 */
	public function testFaviconsInDevMode(): void
	{
		$this->setDevMode();

		$assets   = new Assets();
		$base     = 'http://sandbox.test:3000';
		$favicons = $assets->favicons();

		$this->assertSame($base . '/apple-touch-icon.png', $favicons[0]['href']);
		$this->assertSame($base . '/favicon.png', $favicons[1]['href']);
		$this->assertSame($base . '/favicon.svg', $favicons[2]['href']);
		$this->assertSame($base . '/apple-touch-icon-dark.png', $favicons[3]['href']);
		$this->assertSame($base . '/favicon-dark.png', $favicons[4]['href']);
	}

	/**
	 * @covers ::favicons
	 */
	public function testFaviconsWithCustomArraySetup(): void
	{
		// array
		$this->app->clone([
			'options' => [
				'panel' => [
					'favicon' => [
						[
							'rel'  => 'shortcut icon',
							'type' => 'image/svg+xml',
							'href' => 'assets/my-favicon.svg',
						],
						[
							'rel'  => 'alternate icon',
							'type' => 'image/png',
							'href' => 'assets/my-favicon.png',
						]
					]
				]
			]
		]);

		// default asset setup
		$assets  = new Assets();
		$favicons = $assets->favicons();

		// icons
		$this->assertSame('/assets/my-favicon.svg', $favicons[0]['href']);
		$this->assertSame('/assets/my-favicon.png', $favicons[1]['href']);
	}

	/**
	 * @covers ::favicons
	 */
	public function testFaviconsWithCustomStringSetup(): void
	{
		// array
		$this->app->clone([
			'options' => [
				'panel' => [
					'favicon' => 'assets/favicon.ico'
				]
			]
		]);

		// default asset setup
		$assets  = new Assets();
		$favicons = $assets->favicons();

		// icons
		$this->assertSame('shortcut icon', $favicons[0]['rel']);
		$this->assertSame('image/x-icon', $favicons[0]['type']);
		$this->assertSame('/assets/favicon.ico', $favicons[0]['href']);
	}

	/**
	 * @covers ::favicons
	 */
	public function testFaviconsWithCustomInvalidSetup(): void
	{
		// array
		$this->app->clone([
			'options' => [
				'panel' => [
					'favicon' => 5
				]
			]
		]);

		$assets = new Assets();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid panel.favicon option');

		$assets->favicons();
	}

	/**
	 * @covers ::favicons
	 */
	public function testFaviconsWithCustomUrl(): void
	{
		$this->setDevMode();
		$this->setCustomUrl();

		// default asset setup
		$assets   = new Assets();
		$base     = 'http://localhost:3000';
		$favicons = $assets->favicons();

		// favicons
		$this->assertSame($base . '/apple-touch-icon.png', $favicons[0]['href']);
		$this->assertSame($base . '/favicon.png', $favicons[1]['href']);
		$this->assertSame($base . '/favicon.svg', $favicons[2]['href']);
	}

	/**
	 * @todo Remove backward compatibility part test in v5
	 */
	public function testFaviconsWithRelIndex(): void
	{
		// array
		$this->app->clone([
			'options' => [
				'panel' => [
					'favicon' => [
						'shortcut icon' => [
							'type' => 'image/svg+xml',
							'url'  => 'assets/my-favicon.svg',
						],
						'alternate icon' => [
							'type' => 'image/png',
							'url'  => 'assets/my-favicon.png',
						]
					]
				]
			]
		]);

		// default asset setup
		$assets  = new Assets();
		$favicons = $assets->favicons();

		// icons
		$this->assertSame('shortcut icon', $favicons[0]['rel']);
		$this->assertSame('/assets/my-favicon.svg', $favicons[0]['href']);
		$this->assertSame('alternate icon', $favicons[1]['rel']);
		$this->assertSame('/assets/my-favicon.png', $favicons[1]['href']);
	}

	/**
	 * @todo Remove backward compatibility part test in v5
	 */
	public function testFaviconsWithUrlOption(): void
	{
		// array
		$this->app->clone([
			'options' => [
				'panel' => [
					'favicon' => [
						[
							'rel'  => 'shortcut icon',
							'type' => 'image/svg+xml',
							'url'  => 'assets/my-favicon.svg',
						],
						[
							'rel'  => 'alternate icon',
							'type' => 'image/png',
							'url'  => 'assets/my-favicon.png',
						]
					]
				]
			]
		]);

		// default asset setup
		$assets  = new Assets();
		$favicons = $assets->favicons();

		// icons
		$this->assertSame('/assets/my-favicon.svg', $favicons[0]['href']);
		$this->assertSame('/assets/my-favicon.png', $favicons[1]['href']);
	}

	/**
	 * @covers ::icons
	 */
	public function testIcons(): void
	{
		$assets = new Assets();
		$icons = $assets->icons();

		$this->assertNotNull($icons);
		$this->assertTrue(strpos($icons, '<svg', 0) !== false);
	}

	/**
	 * @covers ::js
	 */
	public function testJs(): void
	{
		// default asset setup
		$assets  = new Assets();
		$base    = '/media/panel/' . $this->app->versionHash();
		$js     = $assets->js();

		// js
		$this->assertSame($base . '/js/vue.min.js', $js['vue']['src']);
		$this->assertSame($base . '/js/vendor.min.js', $js['vendor']['src']);
		$this->assertSame('module', $js['vendor']['type']);

		$this->assertSame($base . '/js/plugins.js', $js['pluginloader']['src']);
		$this->assertSame('module', $js['pluginloader']['type']);

		$this->assertSame('/media/plugins/index.js?0', $js['plugins']['src']);
		$this->assertTrue($js['plugins']['defer']);
		$this->assertArrayNotHasKey('type', $js['plugins']);

		$this->assertSame($base . '/js/index.min.js', $js['index']['src']);
		$this->assertSame('module', $js['index']['type']);
	}

	/**
	 * @covers ::js
	 */
	public function testJsInDevMode(): void
	{
		$this->setDevMode();

		$assets = new Assets();
		$base   = 'http://sandbox.test:3000';
		$js     = $assets->js();

		$this->assertSame([
			'vue' => $base . '/node_modules/vue/dist/vue.js',
			'pluginloader' => $base . '/js/plugins.js',
			'plugins' => '/media/plugins/index.js?0',
			'index' => $base . '/src/index.js',
			'vite' => $base . '/@vite/client'
		], array_map(fn ($js) => $js['src'], $js));
	}

	/**
	 * @covers ::js
	 */
	public function testJsWithCustomFile(): void
	{
		touch(static::TMP . '/panel.js');
		touch(static::TMP . '/foo.js');

		// single
		$this->app->clone([
			'options' => [
				'panel' => [
					'js' => 'panel.js'
				]
			]
		]);

		$assets = new Assets();
		$assets = $assets->custom('panel.js');
		$this->assertStringContainsString('/panel.js', $assets['custom-0']);


		// multiple
		$this->app->clone([
			'options' => [
				'panel' => [
					'js' => ['panel.js', 'foo.js', $url = 'https://getkirby.com/bar.js']
				]
			]
		]);

		$assets = new Assets();
		$assets = $assets->custom('panel.js');
		$this->assertStringContainsString('/panel.js', $assets['custom-0']);
		$this->assertStringContainsString('/foo.js', $assets['custom-1']);
		$this->assertSame($url, $assets['custom-2']);
	}

	/**
	 * @covers ::js
	 */
	public function testJsWithCustomFileMissing(): void
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'js' => 'panel.js'
				]
			]
		]);

		$assets = new Assets();
		$this->assertEmpty($assets->custom('panel.js'));
	}

	/**
	 * @covers ::js
	 */
	public function testJsWithCustomUrl(): void
	{
		$this->setDevMode();
		$this->setCustomUrl();

		// default asset setup
		$assets = new Assets();
		$base   = 'http://localhost:3000';
		$js     = $assets->js();

		// js
		$this->assertSame([
			'vue' => $base . '/node_modules/vue/dist/vue.js',
			'pluginloader' => $base . '/js/plugins.js',
			'plugins' => '/media/plugins/index.js?0',
			'index' => $base . '/src/index.js',
			'vite' => $base . '/@vite/client'
		], array_map(fn ($js) => $js['src'], $js));
	}

	/**
	 * @covers ::link
	 */
	public function testLink(): void
	{
		$assets = new Assets();

		// create links
		$link = $assets->link();
		$this->assertTrue($link);

		// try again to create links, should be return false
		$link = $assets->link();
		$this->assertFalse($link);
	}

	public function testVue()
	{
		$assets = new Assets();
		$vue    = $assets->vue();

		$this->assertSame('/media/panel/' . $this->app->versionHash() . '/js/vue.min.js', $vue);
	}

	public function testVueInDevMode()
	{
		$assets = new Assets();
		$vue    = $assets->vue(production: false);

		$this->assertSame('/media/panel/' . $this->app->versionHash() . '/node_modules/vue/dist/vue.js', $vue);
	}

	public function testVueWithDisabledTemplateCompiler()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'vue' => [
						'compiler' => false
					]
				]
			]
		]);

		$assets = new Assets();
		$vue    = $assets->vue();

		$this->assertSame('/media/panel/' . $this->app->versionHash() . '/js/vue.runtime.min.js', $vue);
	}

	public function testVueWithDisabledTemplateCompilerInDevMode()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'vue' => [
						'compiler' => false
					]
				]
			]
		]);

		$assets = new Assets();
		$vue    = $assets->vue(production: false);

		$this->assertSame('/media/panel/' . $this->app->versionHash() . '/node_modules/vue/dist/vue.runtime.js', $vue);
	}
}
