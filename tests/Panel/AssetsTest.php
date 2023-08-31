<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Assets
 */
class AssetsTest extends TestCase
{
	protected $app;
	protected $tmp = __DIR__ . '/tmp';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp,
			]
		]);

		Dir::make($this->tmp);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove($this->tmp);

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
		F::write($app->roots()->panel() . '/.vite-running', '');
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
	 */
	public function testCssWithCustomFile(): void
	{
		// valid
		F::write($this->tmp . '/panel.css', '');

		$this->app->clone([
			'options' => [
				'panel' => [
					'css' => 'panel.css'
				]
			]
		]);

		$assets = new Assets();

		$this->assertStringContainsString(
			'/panel.css',
			$assets->custom('panel.css')
		);
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
		$this->assertNull($assets->custom('panel.css'));
	}

	/**
	 * @covers ::css
	 */
	public function testCssWithCustomUrl(): void
	{
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
		F::write($this->tmp . '/assets/panel.css', 'test');
		F::write($this->tmp . '/assets/panel.js', 'test');

		$assets   = new Assets();
		$external = $assets->external();

		$this->assertTrue(Str::contains($external['css']['custom'], 'assets/panel.css'));
		$this->assertTrue(Str::contains($external['js']['custom']['src'], 'assets/panel.js'));
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
		$this->assertSame($base . '/apple-touch-icon.png', $favicons['apple-touch-icon']['url']);
		$this->assertSame($base . '/favicon.svg', $favicons['shortcut icon']['url']);
		$this->assertSame($base . '/favicon.png', $favicons['alternate icon']['url']);
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

		$this->assertSame($base . '/apple-touch-icon.png', $favicons['apple-touch-icon']['url']);
		$this->assertSame($base . '/favicon.svg', $favicons['shortcut icon']['url']);
		$this->assertSame($base . '/favicon.png', $favicons['alternate icon']['url']);
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
		$this->assertSame('assets/my-favicon.svg', $favicons['shortcut icon']['url']);
		$this->assertSame('assets/my-favicon.png', $favicons['alternate icon']['url']);
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
		$this->assertSame('image/x-icon', $favicons['shortcut icon']['type']);
		$this->assertSame('assets/favicon.ico', $favicons['shortcut icon']['url']);
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
		$this->setCustomUrl();

		// default asset setup
		$assets   = new Assets();
		$base     = 'http://localhost:3000';
		$favicons = $assets->favicons();

		// favicons
		$this->assertSame($base . '/apple-touch-icon.png', $favicons['apple-touch-icon']['url']);
		$this->assertSame($base . '/favicon.svg', $favicons['shortcut icon']['url']);
		$this->assertSame($base . '/favicon.png', $favicons['alternate icon']['url']);
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
		// valid
		F::write($this->tmp . '/panel.js', '');

		$this->app->clone([
			'options' => [
				'panel' => [
					'js' => 'panel.js'
				]
			]
		]);

		$assets = new Assets();

		$this->assertStringContainsString(
			'/panel.js',
			$assets->custom('panel.js')
		);
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
		$this->assertNull($assets->custom('panel.js'));
	}

	/**
	 * @covers ::js
	 */
	public function testJsWithCustomUrl(): void
	{
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
}
