<?php

namespace Kirby\Panel\Areas;

use Kirby\Cms\App;

class SystemTest extends AreaTestCase
{
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
				'label' => 'License',
				'value' => 'Unregistered',
				'theme' => 'negative',
				'dialog' => 'registration'
			],
			[
				'label' => 'Version',
				'value' => $this->app->version(),
				'link' => 'https://github.com/getkirby/kirby/releases/tag/' . $this->app->version()
			],
			[
				'label' => 'PHP',
				'value' => phpversion()
			],
			[
				'label' => 'Server',
				'value' => 'php'
			],
		], $props['environment']);
		$this->assertSame([], $props['plugins']);
		$this->assertSame([], $props['security']);
		$this->assertSame([
			'content' => 'https://example.com/content/site.txt',
			'git' => null,
			'kirby' => null,
			'site' => 'https://example.com/site'
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

		$this->assertSame([
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
				'id'   => 'https',
				'text' => 'We recommend HTTPS for all your sites',
				'link' => 'https://getkirby.com/security/https'
			]
		], $props['security']);
	}

	public function testViewWithPlugins(): void
	{
		App::plugin('getkirby/test', [
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

		$view     = $this->view('system');
		$expected = [
			[
				'author'  => 'A, B',
				'license' => '–',
				'name'    => [
					'text' => 'getkirby/test',
					'href' => 'https://getkirby.com'
				],
				'version' => '1.0.0'
			]
		];

		$this->assertSame($expected, $view['props']['plugins']);

		App::destroy();
	}
}
