<?php

namespace Kirby\Panel;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Assets::class)]
class AssetsTest extends TestCase
{
	public const string TMP               = KIRBY_TMP_DIR . '/Panel.Assets';
	public const string VITE_RUNNING_PATH = KIRBY_DIR . '/panel/.vite-running';

	protected bool $hadViteRunning;

	public function setUp(): void
	{
		parent::setUp();

		// initialize development mode to a known state
		$this->hadViteRunning = is_file(static::VITE_RUNNING_PATH);
		F::remove(static::VITE_RUNNING_PATH);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		// reset development mode
		if ($this->hadViteRunning === true) {
			touch(static::VITE_RUNNING_PATH);
		} else {
			F::remove(static::VITE_RUNNING_PATH);
		}
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

	public function setDevMode(): void
	{
		$this->app->clone([
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

	public function setPluginDevMode(): void
	{
		Dir::make($this->app->root('plugins') . '/test');
		touch($this->app->root('plugins') . '/test/index.dev.js');
	}

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

	public function testCssInDevMode(): void
	{
		$this->setDevMode();

		$assets = new Assets();
		$css    = $assets->css();

		// css
		$this->assertSame(['plugins' => '/media/plugins/index.css?0'], $css);
	}

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

	public function testIcons(): void
	{
		$assets = new Assets();
		$icons = $assets->icons();

		$this->assertNotNull($icons);
		$this->assertTrue(str_contains($icons, '<svg'));
	}

	public function testImportMaps(): void
	{
		$assets = new Assets();
		$importMaps = $assets->importMaps();

		$this->assertSame('/media/panel/' . $this->app->versionHash() . '/js/vue.esm-browser.prod.js', $importMaps['vue']);
	}

	public function testJs(): void
	{
		// default asset setup
		$assets  = new Assets();
		$base    = '/media/panel/' . $this->app->versionHash();
		$js     = $assets->js();

		// js
		$this->assertSame($base . '/js/vendor.min.js', $js['vendor']['src']);
		$this->assertSame('module', $js['vendor']['type']);

		$this->assertSame($base . '/js/plugins.js', $js['plugin-registry']['src']);
		$this->assertSame($base . '/js/index.min.js', $js['index']['src']);
	}

	public function testJsInDevMode(): void
	{
		$this->setDevMode();

		$assets = new Assets();
		$base   = 'http://sandbox.test:3000';
		$js     = $assets->js();

		$this->assertSame([
			'plugin-registry' => $base . '/js/plugins.js',
			'index'           => $base . '/src/index.js',
			'vite'            => $base . '/@vite/client'
		], array_map(fn ($js) => $js['src'], $js));
	}

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
			'plugin-registry' => $base . '/js/plugins.js',
			'index'           => $base . '/src/index.js',
			'vite'            => $base . '/@vite/client'
		], array_map(fn ($js) => $js['src'], $js));
	}

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

	public function testVue(): void
	{
		$assets = new Assets();
		$vue    = $assets->vue();

		$this->assertSame('/media/panel/' . $this->app->versionHash() . '/js/vue.esm-browser.prod.js', $vue);
	}

	public function testVueInDevMode(): void
	{
		$this->setDevMode();

		$assets = new Assets();
		$vue    = $assets->vue();

		$this->assertSame('http://sandbox.test:3000/node_modules/vue/dist/vue.esm-browser.js', $vue);
	}

	public function testVueInPluginDevMode(): void
	{
		$this->setPluginDevMode();

		$assets = new Assets();
		$vue    = $assets->vue();

		$this->assertSame('/media/panel/' . $this->app->versionHash() . '/js/vue.esm-browser.js', $vue);
	}
}
