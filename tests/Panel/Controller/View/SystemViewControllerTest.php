<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\App;
use Kirby\Cms\System\UpdateStatus;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\Stats;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Helper mock class to not repeat the same code in the tests
 */
class SystemMock
{
	public static function compilerWarning(): array
	{
		return [
			'id'    => 'vue-compiler',
			'link'  => 'https://getkirby.com/security/vue-compiler',
			'text'  => 'The Vue template compiler is enabled',
			'theme' => 'notice'
		];
	}

	public static function customWarning(): array
	{
		return [
			'text'  => 'This is a very important announcement!',
			'kirby' => '*',
			'php'   => '*'
		];
	}

	public static function unknownLicense(): array
	{
		return [
			'link'   => null,
			'name'   => '-',
			'status' => static::unknownLicenseStatus()
		];
	}

	public static function unknownLicenseStatus(): array
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
}

#[CoversClass(SystemViewController::class)]
class SystemViewControllerTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/system';
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.SystemViewController';

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

		$this->app = $this->app->clone([
			'options' => [
				'url' => 'https://example.com'
			]
		]);
	}

	protected function installPlugins(): void
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
	}

	public function testButtons(): void
	{
		$controller = new SystemViewController();
		$buttons    = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
	}

	public function testExceptions(): void
	{
		$controller = new SystemViewController();
		$exceptions = $controller->exceptions();
		$this->assertIsArray($exceptions);
		$this->assertCount(0, $exceptions);
	}

	public function testExceptionsPlugins(): void
	{
		$this->installPlugins();
		$controller = new SystemViewController();
		$exceptions = $controller->exceptions();
		$this->assertCount(0, $exceptions);
	}

	public function testExceptionsPluginsDebug(): void
	{
		$this->installPlugins();
		$this->app = $this->app->clone([
			'options' => [
				'debug' => true
			]
		]);

		$controller = new SystemViewController();
		$exceptions = $controller->exceptions();
		$this->assertSame([
			'Could not load update data for plugin getkirby/private: Couldn\'t open file ' .
			static::FIXTURES . '/plugins/getkirby/private.json',
			'Could not load update data for plugin getkirby/unknown: Couldn\'t open file ' .
			static::FIXTURES . '/plugins/getkirby/unknown.json',
		], $exceptions);
	}

	public function testExceptionsLocal(): void
	{
		$this->app = $this->app->clone([
			'server' => [
				'REMOTE_ADDR' => '127.0.0.1',
			]
		]);

		$controller = new SystemViewController();
		$this->assertSame([], $controller->exceptions());
	}

	public function testLoad(): void
	{
		$controller = new SystemViewController();
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-system-view', $view->component);

		$props = $view->props();
		$this->assertIsArray($props['buttons']);
		$this->assertIsArray($props['environment']);
		$this->assertArrayHasKey('plugins', $props);
		$this->assertArrayHasKey('exceptions', $props);
		$this->assertSame($this->app->system()->info(), $props['info']);
		$this->assertArrayHasKey('security', $props);
		$this->assertArrayHasKey('urls', $props);
	}

	public function testPlugins(): void
	{
		$controller = new SystemViewController();
		$plugins    = $controller->plugins();
		$this->assertIsArray($plugins);
		$this->assertCount(0, $plugins);

		$this->installPlugins();

		$controller = new SystemViewController();
		$plugins    = $controller->plugins();
		$this->assertCount(3, $plugins);

		$expected = [
			[
				'author'  => '–',
				'license' => SystemMock::unknownLicense(),
				'name'    => [
					'text' => 'getkirby/private',
					'href' => null
				],
				'status'  => SystemMock::unknownLicenseStatus(),
				'version' => [
					'currentVersion' => '?',
					'icon'           => 'question',
					'label'          => 'Could not check for updates',
					'latestVersion'  => '?',
					'pluginName'     => 'getkirby/private',
					'theme'          => 'passive',
					'url'            => null
				]
			],
			[
				'author'  => 'A, B',
				'license' => SystemMock::unknownLicense(),
				'name'    => [
					'text' => 'getkirby/public',
					'href' => 'https://plugins.getkirby.com/getkirby/public'
				],
				'status'  => SystemMock::unknownLicenseStatus(),
				'version' => [
					'currentVersion' => '1.0.0',
					'icon'           => 'info',
					'label'          => 'Free update 88888.8.8 available',
					'latestVersion'  => '99999.9.9',
					'pluginName'     => 'getkirby/public',
					'theme'          => 'info',
					'url'            => 'https://github.com/getkirby/public-plugin/releases/tag/88888.8.8'
				]
			],
			[
				'author'  => '–',
				'license' => SystemMock::unknownLicense(),
				'name'    => [
					'text' => 'getkirby/unknown',
					'href' => null
				],
				'status'  => SystemMock::unknownLicenseStatus(),
				'version' => [
					'currentVersion' => '1.0.0',
					'icon'           => 'question',
					'label'          => 'Could not check for updates',
					'latestVersion'  => '?',
					'pluginName'     => 'getkirby/unknown',
					'theme'          => 'passive',
					'url'            => null
				]
			]
		];

		$this->assertSame($expected, $plugins);
	}

	public function testPluginsWithoutUpdateCheck(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'updates' => false
			]
		]);

		$this->installPlugins();

		$controller = new SystemViewController();
		$plugins    = $controller->plugins();
		$this->assertSame('1.0.0', $plugins[1]['version']);
	}

	public function testSecurity(): void
	{
		$controller = new SystemViewController();
		$security   = $controller->security();
		$this->assertSame([
			SystemMock::customWarning(),
			SystemMock::compilerWarning()
		], $security);
	}

	public function testSecurityDebug(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'debug' => true
			]
		]);

		$controller = new SystemViewController();
		$security   = $controller->security();
		$this->assertSame([
			SystemMock::customWarning(),
			[
				'id'    => 'debug',
				'icon'  => 'alert',
				'theme' => 'negative',
				'text'  => 'Debugging must be turned off in production',
				'link'  => 'https://getkirby.com/security/debug'
			],
			SystemMock::compilerWarning()
		], $security);
	}

	public function testSecurityLocal(): void
	{
		$this->app = $this->app->clone([
			'server' => [
				'REMOTE_ADDR' => '127.0.0.1',
			]
		]);

		$controller = new SystemViewController();
		$security   = $controller->security();
		$this->assertSame([
			SystemMock::customWarning(),
			[
				'id'    => 'local',
				'icon'  => 'info',
				'theme' => 'info',
				'text'  => 'The site is running locally with relaxed security checks'
			],
			SystemMock::compilerWarning()
		], $security);
	}

	public function testSecurityWithoutCompilerWarning(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel.vue.compiler' => true
			]
		]);

		$controller = new SystemViewController();
		$security   = $controller->security();
		$this->assertSame([
			SystemMock::customWarning()
		], $security);
	}

	public function testSecurityWithoutUpdate(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'updates' => false
			]
		]);

		$controller = new SystemViewController();
		$security   = $controller->security();
		$this->assertSame([
			SystemMock::compilerWarning()
		], $security);
	}

	public function testSecurityMissingHttps(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'url' => 'http://example.com'
			]
		]);

		$controller = new SystemViewController();
		$security   = $controller->security();
		$this->assertSame([
			SystemMock::customWarning(),
			[
				'id'   => 'https',
				'text' => 'We recommend HTTPS for all your sites',
				'link' => 'https://getkirby.com/security/https'
			],
			SystemMock::compilerWarning()
		], $security);
	}

	public function testStats(): void
	{
		$controller = new SystemViewController();
		$stats      = $controller->stats();
		$this->assertInstanceOf(Stats::class, $stats);
		$this->assertCount(4, $stats->reports());
	}

	public function testUrls(): void
	{
		$controller = new SystemViewController();
		$urls       = $controller->urls();
		$this->assertCount(4, $urls);
	}

	public function testUrlsLocal(): void
	{
		$this->app = $this->app->clone([
			'server' => [
				'REMOTE_ADDR' => '127.0.0.1',
			]
		]);

		$controller = new SystemViewController();
		$urls       = $controller->urls();
		$this->assertCount(0, $urls);
	}
}
