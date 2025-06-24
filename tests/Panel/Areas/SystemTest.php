<?php

namespace Kirby\Panel\Areas;

use Kirby\Cms\App;
use Kirby\Cms\System\UpdateStatus;

class SystemTest extends AreaTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/SystemTest';

	protected static string $host;

	public static function setUpBeforeClass(): void
	{
		static::$host = UpdateStatus::$host;
		UpdateStatus::$host = 'file://' . static::FIXTURES;
	}

	public static function tearDownAfterClass(): void
	{
		UpdateStatus::$host = static::$host;
	}

	public function setUp(): void
	{
		parent::setUp();

		$this->app([
			'options' => [
				'url' => 'https://example.com'
			]
		]);
		$this->install();
	}

	protected function compilerWarning(): array
	{
		return [
			'id'    => 'vue-compiler',
			'link'  => 'https://getkirby.com/security/vue-compiler',
			'text'  => 'The Vue template compiler is enabled',
			'theme' => 'notice'
		];
	}

	protected function customWarning(): array
	{
		return [
			'text'  => 'This is a very important announcement!',
			'kirby' => '*',
			'php'   => '*'
		];
	}

	protected function unknownLicense(): array
	{
		return [
			'link'   => null,
			'name'   => '-',
			'status' => $this->unknownLicenseStatus()
		];
	}

	protected function unknownLicenseStatus(): array
	{
		return [
			'dialog' => null,
			'drawer' => null,
			'icon'   => 'question',
			'label'  => 'Unknown',
			'link'	 => null,
			'theme'  => 'passive',
			'value'  => 'unknown',
		];
	}

	public function testViewWithoutAuthentication(): void
	{
		$this->assertRedirect('system', 'login');
	}

	public function testView(): void
	{
		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame('system', $view['id']);
		$this->assertSame('System', $view['title']);
		$this->assertSame('k-system-view', $view['component']);
		$this->assertSame([
			[
				'label'  => 'Please activate your license',
				'value'  => 'Unregistered',
				'theme'  => 'love',
				'icon'   => 'key',
				'dialog' => 'registration'
			],
			[
				'label' => 'Free update 88888.8.8 available',
				'value' => $this->app->version(),
				'link'  => 'https://getkirby.com/releases/88888.8.8',
				'theme' => 'info',
				'icon'  => 'info'
			],
			[
				'label' => 'PHP',
				'value' => phpversion(),
				'icon'  => 'code'
			],
			[
				'label' => 'Server',
				'value' => 'php',
				'icon'  => 'server'
			],
		], $props['environment']);
		$this->assertSame([], $props['exceptions']);
		$this->assertSame([], $props['plugins']);
		$this->assertSame([
			[
				'text'  => 'This is a very important announcement!',
				'kirby' => '*',
				'php'   => '*'
			],
			[
				'id'    => 'vue-compiler',
				'link' => 'https://getkirby.com/security/vue-compiler',
				'text' => 'The Vue template compiler is enabled',
				'theme' => 'notice',
			]
		], $props['security']);
		$this->assertSame([
			'content' => 'https://example.com/content/site.txt',
			'git'     => null,
			'kirby'   => null,
			'site'    => 'https://example.com/site'
		], $props['urls']);
	}

	public function testViewLocal(): void
	{
		$this->app([
			'server' => [
				'REMOTE_ADDR' => '127.0.0.1',
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame([], $props['exceptions']);
		$this->assertSame([
			$this->customWarning(),
			[
				'id'    => 'local',
				'icon'  => 'info',
				'theme' => 'info',
				'text'  => 'The site is running locally with relaxed security checks'
			],
			$this->compilerWarning()
		], $props['security']);
	}

	public function testViewDebug(): void
	{
		$this->app([
			'options' => [
				'debug' => true
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame([], $props['exceptions']);
		$this->assertSame([
			$this->customWarning(),
			[
				'id'    => 'debug',
				'icon'  => 'alert',
				'theme' => 'negative',
				'text'  => 'Debugging must be turned off in production',
				'link'  => 'https://getkirby.com/security/debug'
			],
			$this->compilerWarning()
		], $props['security']);
	}

	public function testViewWithoutConfiguredTemplateCompiler(): void
	{
		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertArrayHasKey(1, $props['security']);
		$this->assertSame($this->compilerWarning(), $props['security'][1]);
	}

	public function testViewWithConfiguredTemplateCompiler(): void
	{
		$this->app([
			'options' => [
				'panel.vue.compiler' => true
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertArrayNotHasKey(1, $props['security']);
	}

	public function testViewHttps(): void
	{
		$this->app([
			'options' => [
				'url' => 'http://example.com'
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame([
			$this->customWarning(),
			[
				'id'   => 'https',
				'text' => 'We recommend HTTPS for all your sites',
				'link' => 'https://getkirby.com/security/https'
			],
			$this->compilerWarning()
		], $props['security']);
	}

	public function testViewWithPlugins(): void
	{
		App::plugin('getkirby/private', [
			'info' => []
		]);

		App::plugin('getkirby/public', [
			'info' => [
				'authors' => [
					[
						'name' => 'A'
					],
					[
						'name' => 'B'
					]
				],
				'homepage' => 'https://getkirby.com',
				'version'  => '1.0.0',
			]
		]);

		App::plugin('getkirby/unknown', [
			'info' => [
				'version' => '1.0.0'
			]
		]);

		$this->login();

		$view     = $this->view('system');
		$expected = [
			[
				'author'  => '–',
				'license' => $this->unknownLicense(),
				'name'    => [
					'text' => 'getkirby/private',
					'href' => null
				],
				'status'  => $this->unknownLicenseStatus(),
				'version' => [
					'currentVersion' => '?',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'getkirby/private',
					'theme' => 'passive',
					'url' => null
				]
			],
			[
				'author'  => 'A, B',
				'license' => $this->unknownLicense(),
				'name'    => [
					'text' => 'getkirby/public',
					'href' => 'https://getkirby.com'
				],
				'status'  => $this->unknownLicenseStatus(),
				'version' => [
					'currentVersion' => '1.0.0',
					'icon' => 'info',
					'label' => 'Free update 88888.8.8 available',
					'latestVersion' => '99999.9.9',
					'pluginName' => 'getkirby/public',
					'theme' => 'info',
					'url' => 'https://github.com/getkirby/public-plugin/releases/tag/88888.8.8'
				]
			],
			[
				'author'  => '–',
				'license' => $this->unknownLicense(),
				'name'    => [
					'text' => 'getkirby/unknown',
					'href' => null
				],
				'status'  => $this->unknownLicenseStatus(),
				'version' => [
					'currentVersion' => '1.0.0',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'getkirby/unknown',
					'theme' => 'passive',
					'url' => null
				]
			]
		];

		$this->assertSame($expected, $view['props']['plugins']);
		$this->assertSame([], $view['props']['exceptions']);
	}

	public function testViewWithPluginsDebug(): void
	{
		App::plugin('getkirby/private', [
			'info' => []
		]);

		App::plugin('getkirby/public', [
			'info' => [
				'authors' => [
					[
						'name' => 'A'
					],
					[
						'name' => 'B'
					]
				],
				'homepage' => 'https://getkirby.com',
				'version'  => '1.0.0',
			]
		]);

		App::plugin('getkirby/unknown', [
			'info' => [
				'version' => '1.0.0'
			]
		]);

		$this->app([
			'options' => [
				'debug' => true
			]
		]);

		$this->login();

		$view     = $this->view('system');
		$expected = [
			[
				'author'  => '–',
				'license' => $this->unknownLicense(),
				'name'    => [
					'text' => 'getkirby/private',
					'href' => null
				],
				'status'  => $this->unknownLicenseStatus(),
				'version' => [
					'currentVersion' => '?',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'getkirby/private',
					'theme' => 'passive',
					'url' => null
				]
			],
			[
				'author'  => 'A, B',
				'license' => $this->unknownLicense(),
				'name'    => [
					'text' => 'getkirby/public',
					'href' => 'https://getkirby.com'
				],
				'status'  => $this->unknownLicenseStatus(),
				'version' => [
					'currentVersion' => '1.0.0',
					'icon' => 'info',
					'label' => 'Free update 88888.8.8 available',
					'latestVersion' => '99999.9.9',
					'pluginName' => 'getkirby/public',
					'theme' => 'info',
					'url' => 'https://github.com/getkirby/public-plugin/releases/tag/88888.8.8'
				]
			],
			[
				'author'  => '–',
				'license' => $this->unknownLicense(),
				'name'    => [
					'text' => 'getkirby/unknown',
					'href' => null
				],
				'status' => $this->unknownLicenseStatus(),
				'version' => [
					'currentVersion' => '1.0.0',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'getkirby/unknown',
					'theme' => 'passive',
					'url' => null
				]
			]
		];

		$this->assertSame($expected, $view['props']['plugins']);
		$this->assertSame([
			'Could not load update data for plugin getkirby/private: Couldn\'t open file ' .
			static::FIXTURES . '/plugins/getkirby/private.json',
			'Could not load update data for plugin getkirby/unknown: Couldn\'t open file ' .
			static::FIXTURES . '/plugins/getkirby/unknown.json',
		], $view['props']['exceptions']);
	}

	public function testViewWithoutUpdateCheck(): void
	{
		$this->app([
			'options' => [
				'updates' => false,
				'panel.vue.compiler' => true
			]
		]);

		App::plugin('getkirby/public', [
			'info' => [
				'authors' => [
					[
						'name' => 'A'
					],
					[
						'name' => 'B'
					]
				],
				'homepage' => 'https://getkirby.com',
				'version'  => '1.0.0',
			]
		]);

		$this->login();

		$view  = $this->view('system');
		$props = $view['props'];

		$this->assertSame([
			[
				'label'  => 'Please activate your license',
				'value'  => 'Unregistered',
				'theme'  => 'love',
				'icon'   => 'key',
				'dialog' => 'registration'
			],
			[
				'label' => 'Version',
				'value' => $this->app->version(),
				'link'  => 'https://github.com/getkirby/kirby/releases/tag/' . $this->app->version(),
				'theme' => null,
				'icon'  => 'info'
			],
			[
				'label' => 'PHP',
				'value' => phpversion(),
				'icon'  => 'code'
			],
			[
				'label' => 'Server',
				'value' => 'php',
				'icon'  => 'server'
			],
		], $props['environment']);

		$this->assertSame([], $props['security']);

		$this->assertSame([
			[
				'author'  => 'A, B',
				'license' => $this->unknownLicense(),
				'name'    => [
					'text' => 'getkirby/public',
					'href' => 'https://getkirby.com'
				],
				'status'  => $this->unknownLicenseStatus(),
				'version' => '1.0.0'
			]
		], $props['plugins']);
		$this->assertSame([], $props['exceptions']);
	}
}
