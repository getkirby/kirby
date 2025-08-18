<?php

namespace Kirby\Panel\Controller\Drawer;

use Exception;
use Kirby\Cms\Page;
use Kirby\Cms\Section;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SectionDrawerController::class)]
class SectionDrawerControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Drawer.SectionDrawerController';

	protected function section(): Section
	{
		$model = new Page(['slug' => 'test']);

		return new class ('info', ['model' => $model]) extends Section {
			public function drawers(): array
			{
				return [
					[
						'pattern' => 'a',
						'load'    => fn () => ['drawer-a' => 'load'],
						'submit'  => fn () => ['drawer-a' => 'submit'],
					],
					[
						'pattern' => 'b',
						'load'    => fn () => ['drawer-b' => 'load'],
						'submit'  => fn () => ['drawer-b' => 'submit'],
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

		$controller = SectionDrawerController::factory(
			model: 'pages/test',
			filename: 'test',
			section: 'test'
		);

		$this->assertInstanceOf(SectionDrawerController::class, $controller);
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

		$controller = SectionDrawerController::factory(
			model: 'pages/test',
			filename: 'test.jpg',
			section: 'test',
			path: 'test'
		);

		$this->assertInstanceOf(SectionDrawerController::class, $controller);
		$file = $this->app->page('test')->file('test.jpg');
		$this->assertSame($file, $controller->section->model());
		$this->assertSame('test', $controller->section->name());
		$this->assertSame('test', $controller->path);
	}

	public function testLoad(): void
	{
		$section = $this->section();

		$controller = new SectionDrawerController($section, 'a');
		$response   = $controller->load();
		$this->assertSame(['drawer-a' => 'load'], $response);

		$controller = new SectionDrawerController($section, 'b');
		$response   = $controller->load();
		$this->assertSame(['drawer-b' => 'load'], $response);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "c" and request method: "GET"');

		$controller = new SectionDrawerController($section, 'c');
		$controller->load();
	}

	public function testRoutes(): void
	{
		$section    = $this->section();
		$controller = new SectionDrawerController($section);
		$routes     = $controller->routes();

		$this->assertCount(4, $routes);
		$this->assertSame(['drawer-a' => 'load'], $routes[0]['action']());
		$this->assertSame('GET', $routes[0]['method']);
		$this->assertSame(['drawer-a' => 'submit'], $routes[1]['action']());
		$this->assertSame('POST', $routes[1]['method']);
		$this->assertSame(['drawer-b' => 'load'], $routes[2]['action']());
		$this->assertSame('GET', $routes[2]['method']);
		$this->assertSame(['drawer-b' => 'submit'], $routes[3]['action']());
		$this->assertSame('POST', $routes[3]['method']);
	}

	public function testSubmit(): void
	{
		$section = $this->section();

		$controller = new SectionDrawerController($section, 'a');
		$response   = $controller->submit();
		$this->assertSame(['drawer-a' => 'submit'], $response);

		$controller = new SectionDrawerController($section, 'b');
		$response   = $controller->submit();
		$this->assertSame(['drawer-b' => 'submit'], $response);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "c" and request method: "POST"');

		$controller = new SectionDrawerController($section, 'c');
		$controller->submit();
	}
}
