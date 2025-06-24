<?php

namespace Kirby\Panel\Areas;

use Kirby\Cms\App;

class PluginTest extends AreaTestCase
{
	public function testView(): void
	{
		App::plugin('test/a', [
			'areas' => [
				'foo' => [
					'views' => [
						[
							'pattern' => 'foo',
							'action'  => fn () => [
								'component' => 'k-foo-view'
							]
						]
					]
				]
			]
		]);

		$this->install();
		$this->login();

		$view = $this->view('foo');
		$this->assertSame('k-foo-view', $view['component']);
	}

	public function testViewWhen(): void
	{
		App::plugin('test/a', [
			'areas' => [
				'foo' => [
					'views' => [
						[
							'pattern' => 'foo',
							'when'    => fn () => true,
							'action'  => fn () => [
								'component' => 'k-foo-view',
							]
						],
						[
							'pattern' => 'bar',
							'when'    => fn () => false,
							'action'  => fn () => [
								'component' => 'k-bar-view',
							]
						]
					]
				]
			]
		]);

		$this->install();
		$this->login();

		$view = $this->view('foo');
		$this->assertSame('k-foo-view', $view['component']);

		$view = $this->view('bar');
		$this->assertSame('k-error-view', $view['component']);
	}
}
