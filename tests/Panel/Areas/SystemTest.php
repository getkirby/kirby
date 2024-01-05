<?php

namespace Kirby\Panel\Areas;

use Kirby\Cms\App;
use Kirby\Cms\System\UpdateStatus;

class SystemTest extends AreaTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/SystemTest';

	protected static $host;

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
			]
		], $props['security']);
		$this->assertSame([
			'content' => 'https://example.com/content/site.txt',
			'git'     => null,
			'kirby'   => null,
			'site'    => 'https://example.com/site'
		], $props['urls']);
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
			[
				'text'  => 'This is a very important announcement!',
				'kirby' => '*',
				'php'   => '*'
			],
			[
				'id'   => 'debug',
				'text' => 'Debugging must be turned off in production',
				'link' => 'https://getkirby.com/security/debug'
			]
		], $props['security']);
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
			[
				'text'  => 'This is a very important announcement!',
				'kirby' => '*',
				'php'   => '*'
			],
			[
				'id'   => 'https',
				'text' => 'We recommend HTTPS for all your sites',
				'link' => 'https://getkirby.com/security/https'
			]
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
				'license' => '–',
				'name'    => [
					'text' => 'getkirby/private',
					'href' => null
				],
				'version' => [
					'currentVersion' => '?',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'getkirby/private',
					'theme' => 'notice',
					'url' => null
				]
			],
			[
				'author'  => 'A, B',
				'license' => '–',
				'name'    => [
					'text' => 'getkirby/public',
					'href' => 'https://getkirby.com'
				],
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
				'license' => '–',
				'name'    => [
					'text' => 'getkirby/unknown',
					'href' => null
				],
				'version' => [
					'currentVersion' => '1.0.0',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'getkirby/unknown',
					'theme' => 'notice',
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
				'license' => '–',
				'name'    => [
					'text' => 'getkirby/private',
					'href' => null
				],
				'version' => [
					'currentVersion' => '?',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'getkirby/private',
					'theme' => 'notice',
					'url' => null
				]
			],
			[
				'author'  => 'A, B',
				'license' => '–',
				'name'    => [
					'text' => 'getkirby/public',
					'href' => 'https://getkirby.com'
				],
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
				'license' => '–',
				'name'    => [
					'text' => 'getkirby/unknown',
					'href' => null
				],
				'version' => [
					'currentVersion' => '1.0.0',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '?',
					'pluginName' => 'getkirby/unknown',
					'theme' => 'notice',
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
				'license' => '–',
				'name'    => [
					'text' => 'getkirby/public',
					'href' => 'https://getkirby.com'
				],
				'version' => '1.0.0'
			]
		], $props['plugins']);
		$this->assertSame([], $props['exceptions']);
	}
}
