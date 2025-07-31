<?php

namespace Kirby\Panel\Controller\Dialog;

use Exception;
use Kirby\Form\FieldClass;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldDialogController::class)]
class FieldDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.FieldDialogController';

	protected function field(): FieldClass
	{
		$field = new class () extends FieldClass {
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

		$controller = FieldDialogController::factory(
			model: 'pages/test',
			filename: 'test',
			field: 'test'
		);

		$this->assertInstanceOf(FieldDialogController::class, $controller);
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

		$controller = FieldDialogController::factory(
			model: 'pages/test',
			filename: 'test.jpg',
			field: 'test',
			path: 'test'
		);

		$this->assertInstanceOf(FieldDialogController::class, $controller);
		$file = $this->app->page('test')->file('test.jpg');
		$this->assertSame($file, $controller->field->model());
		$this->assertSame('test', $controller->field->name());
		$this->assertSame('test', $controller->path);
	}

	public function testLoad(): void
	{
		$field = $this->field();

		$controller = new FieldDialogController($field, 'dialogs/a');
		$response   = $controller->load();
		$this->assertSame(['dialog-a' => 'load'], $response);

		$controller = new FieldDialogController($field, 'dialogs/b');
		$response   = $controller->load();
		$this->assertSame(['dialog-b' => 'load'], $response);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "dialogs/c" and request method: "GET"');

		$controller = new FieldDialogController($field, 'dialogs/c');
		$controller->load();
	}

	public function testRoutes(): void
	{
		$field      = $this->field();
		$controller = new FieldDialogController($field);
		$routes     = $controller->routes();

		$this->assertCount(4, $routes);
		$this->assertSame(['dialog-a' => 'load'], $routes[0]['action']());
		$this->assertSame('GET', $routes[0]['method']);
		$this->assertSame(['dialog-a' => 'submit'], $routes[1]['action']());
		$this->assertSame(['dialog-b' => 'load'], $routes[2]['action']());
		$this->assertSame('GET', $routes[2]['method']);
		$this->assertSame(['dialog-b' => 'submit'], $routes[3]['action']());
		$this->assertSame('POST', $routes[3]['method']);
	}

	public function testSubmit(): void
	{
		$field = $this->field();

		$controller = new FieldDialogController($field, 'dialogs/a');
		$response   = $controller->submit();
		$this->assertSame(['dialog-a' => 'submit'], $response);

		$controller = new FieldDialogController($field, 'dialogs/b');
		$response   = $controller->submit();
		$this->assertSame(['dialog-b' => 'submit'], $response);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "dialogs/c" and request method: "POST"');

		$controller = new FieldDialogController($field, 'dialogs/c');
		$controller->submit();
	}
}
