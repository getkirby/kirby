<?php

namespace Kirby\Panel\Controller\Dialog;

use Exception;
use Kirby\Cms\Page;
use Kirby\Cms\Section;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SectionDialogController::class)]
class SectionDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.SectionDialogController';

	protected function section(): Section
	{
		$model = new Page(['slug' => 'test']);

		return new class ('info', ['model' => $model]) extends Section {
			public function dialogs(): array
			{
				return [
					[
						'pattern' => 'a',
						'load'    => fn () => ['dialog-a' => 'load'],
						'submit'  => fn () => ['dialog-a' => 'submit'],
					],
					[
						'pattern' => 'b',
						'load'    => fn () => ['dialog-b' => 'load'],
						'submit'  => fn () => ['dialog-b' => 'submit'],
					]
				];
			}
		};
	}

	public function testFactory(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'blueprints' => [
				'pages/default' => [
					'sections' => [
						'test' => 'info'
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = SectionDialogController::factory(
			model: 'pages/test',
			filename: 'test',
			section: 'test'
		);

		$this->assertInstanceOf(SectionDialogController::class, $controller);
		$page = $this->app->page('test');

		$this->assertSame($page, $controller->section->model());
		$this->assertSame('test', $controller->section->name());
		$this->assertSame('test', $controller->path);
	}

	public function testFactoryForFile(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'files'    => [
							['filename' => 'test.jpg']
						]
					]
				]
			],
			'blueprints' => [
				'files/default' => [
					'sections' => [
						'test' => 'info'
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = SectionDialogController::factory(
			model: 'pages/test',
			filename: 'test.jpg',
			section: 'test',
			path: 'test'
		);

		$this->assertInstanceOf(SectionDialogController::class, $controller);
		$file = $this->app->page('test')->file('test.jpg');
		$this->assertSame($file, $controller->section->model());
		$this->assertSame('test', $controller->section->name());
		$this->assertSame('test', $controller->path);
	}

	public function testLoad(): void
	{
		$section = $this->section();

		$controller = new SectionDialogController($section, 'a');
		$response   = $controller->load();
		$this->assertSame(['dialog-a' => 'load'], $response);

		$controller = new SectionDialogController($section, 'b');
		$response   = $controller->load();
		$this->assertSame(['dialog-b' => 'load'], $response);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "c" and request method: "GET"');

		$controller = new SectionDialogController($section, 'c');
		$controller->load();
	}

	public function testRoutes(): void
	{
		$section    = $this->section();
		$controller = new SectionDialogController($section);
		$routes     = $controller->routes();

		$this->assertCount(4, $routes);
		$this->assertSame(['dialog-a' => 'load'], $routes[0]['action']());
		$this->assertSame('GET', $routes[0]['method']);
		$this->assertSame(['dialog-a' => 'submit'], $routes[1]['action']());
		$this->assertSame('POST', $routes[1]['method']);
		$this->assertSame(['dialog-b' => 'load'], $routes[2]['action']());
		$this->assertSame('GET', $routes[2]['method']);
		$this->assertSame(['dialog-b' => 'submit'], $routes[3]['action']());
		$this->assertSame('POST', $routes[3]['method']);
	}

	public function testSubmit(): void
	{
		$section = $this->section();

		$controller = new SectionDialogController($section, 'a');
		$response   = $controller->submit();
		$this->assertSame(['dialog-a' => 'submit'], $response);

		$controller = new SectionDialogController($section, 'b');
		$response   = $controller->submit();
		$this->assertSame(['dialog-b' => 'submit'], $response);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "c" and request method: "POST"');

		$controller = new SectionDialogController($section, 'c');
		$controller->submit();
	}
}
