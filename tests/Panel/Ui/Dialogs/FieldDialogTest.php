<?php

namespace Kirby\Panel\Ui\Dialogs;

use Exception;
use Kirby\Form\FieldClass;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldDialog::class)]
class FieldDialogTest extends TestCase
{
	protected function field(): FieldClass
	{
		$field = new class () extends FieldClass {
			public function dialogs(): array
			{
				return [
					[
						'pattern' => 'a',
						'load'    => fn () => ['a' => 'load'],
						'submit'  => fn () => ['a' => 'submit'],
					],
					[
						'pattern' => 'b',
						'load'    => fn () => ['b' => 'load'],
						'submit'  => fn () => ['b' => 'submit'],
					]
				];
			}
		};

		return new $field([]);
	}

	public function testForFile(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/default' => [
					'fields' => [
						'test' => [
							'type' => 'text'
						]
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);

		$Dialog = FieldDialog::forFile('pages/test', 'test.jpg', 'test');
		$this->assertInstanceOf(FieldDialog::class, $Dialog);
	}

	public function testForModel(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'fields' => [
						'test' => [
							'type' => 'text'
						]
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$Dialog = FieldDialog::forModel('pages/test', 'test');
		$this->assertInstanceOf(FieldDialog::class, $Dialog);
	}

	public function testRender(): void
	{
		$Dialog = new FieldDialog(
			field: $this->field(),
			path: 'a'
		);
		$this->assertSame(['a' => 'load'], $Dialog->render());

		$Dialog = new FieldDialog(
			field: $this->field(),
			path: 'b'
		);
		$this->assertSame(['b' => 'load'], $Dialog->render());

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "c" and request method: "GET"');

		$Dialog = new FieldDialog(
			field: $this->field(),
			path: 'c'
		);
		$Dialog->render();
	}

	public function testRoutes(): void
	{
		$Dialog = new FieldDialog(
			field: $this->field(),
		);

		$routes = $Dialog->routes();
		$this->assertCount(4, $routes);
		$this->assertSame('a', $routes[0]['pattern']);
		$this->assertArrayNotHasKey('method', $routes[0]);
		$this->assertSame('a', $routes[1]['pattern']);
		$this->assertSame('POST', $routes[1]['method']);
		$this->assertSame('b', $routes[2]['pattern']);
		$this->assertArrayNotHasKey('method', $routes[2]);
		$this->assertSame('b', $routes[3]['pattern']);
		$this->assertSame('POST', $routes[3]['method']);
	}

	public function testSubmit(): void
	{
		$Dialog = new FieldDialog(
			field: $this->field(),
			path: 'a'
		);
		$this->assertSame(['a' => 'submit'], $Dialog->submit());

		$Dialog = new FieldDialog(
			field: $this->field(),
			path: 'b'
		);
		$this->assertSame(['b' => 'submit'], $Dialog->submit());

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "c" and request method: "GET"');

		$Dialog = new FieldDialog(
			field: $this->field(),
			path: 'c'
		);
		$Dialog->render();
	}
}
