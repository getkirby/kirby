<?php

namespace Kirby\Panel\Controller\Drawer;

use Exception;
use Kirby\Form\FieldClass;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldDrawerController::class)]
class FieldDrawerControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Drawer.FieldDrawerController';

	protected function field(): FieldClass
	{
		$field = new class () extends FieldClass {
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

		return new $field([]);
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
					'fields' => [
						'test' => 'text'
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = FieldDrawerController::factory(
			model: 'pages/test',
			filename: 'test',
			field: 'test'
		);

		$this->assertInstanceOf(FieldDrawerController::class, $controller);
		$page = $this->app->page('test');

		$this->assertSame($page, $controller->field->model());
		$this->assertSame('test', $controller->field->name());
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
					'fields' => [
						'test' => 'text'
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = FieldDrawerController::factory(
			model: 'pages/test',
			filename: 'test.jpg',
			field: 'test',
			path: 'test'
		);

		$this->assertInstanceOf(FieldDrawerController::class, $controller);
		$file = $this->app->page('test')->file('test.jpg');
		$this->assertSame($file, $controller->field->model());
		$this->assertSame('test', $controller->field->name());
		$this->assertSame('test', $controller->path);
	}

	public function testLoad(): void
	{
		$field = $this->field();

		$controller = new FieldDrawerController($field, 'a');
		$response   = $controller->load();
		$this->assertSame(['drawer-a' => 'load'], $response);

		$controller = new FieldDrawerController($field, 'b');
		$response   = $controller->load();
		$this->assertSame(['drawer-b' => 'load'], $response);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "c" and request method: "GET"');

		$controller = new FieldDrawerController($field, 'c');
		$controller->load();
	}

	public function testRoutes(): void
	{
		$field      = $this->field();
		$controller = new FieldDrawerController($field);
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
		$field = $this->field();

		$controller = new FieldDrawerController($field, 'a');
		$response   = $controller->submit();
		$this->assertSame(['drawer-a' => 'submit'], $response);

		$controller = new FieldDrawerController($field, 'b');
		$response   = $controller->submit();
		$this->assertSame(['drawer-b' => 'submit'], $response);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "c" and request method: "POST"');

		$controller = new FieldDrawerController($field, 'c');
		$controller->submit();
	}
}
