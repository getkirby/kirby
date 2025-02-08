<?php

namespace Kirby\Panel\Ui\Drawers;

use Exception;
use Kirby\Form\FieldClass;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldDrawer::class)]
class FieldDrawerTest extends TestCase
{
	protected function field(): FieldClass
	{
		$field = new class () extends FieldClass {
			public function drawers(): array
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

		$drawer = FieldDrawer::forFile('pages/test', 'test.jpg', 'test');
		$this->assertInstanceOf(FieldDrawer::class, $drawer);
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

		$drawer = FieldDrawer::forModel('pages/test', 'test');
		$this->assertInstanceOf(FieldDrawer::class, $drawer);
	}

	public function testRender(): void
	{
		$drawer = new FieldDrawer(
			field: $this->field(),
			path: 'a'
		);
		$this->assertSame(['a' => 'load'], $drawer->render());

		$drawer = new FieldDrawer(
			field: $this->field(),
			path: 'b'
		);
		$this->assertSame(['b' => 'load'], $drawer->render());

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "c" and request method: "GET"');

		$drawer = new FieldDrawer(
			field: $this->field(),
			path: 'c'
		);
		$drawer->render();
	}

	public function testRoutes(): void
	{
		$drawer = new FieldDrawer(
			field: $this->field(),
		);

		$routes = $drawer->routes();
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
		$drawer = new FieldDrawer(
			field: $this->field(),
			path: 'a'
		);
		$this->assertSame(['a' => 'submit'], $drawer->submit());

		$drawer = new FieldDrawer(
			field: $this->field(),
			path: 'b'
		);
		$this->assertSame(['b' => 'submit'], $drawer->submit());

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "c" and request method: "GET"');

		$drawer = new FieldDrawer(
			field: $this->field(),
			path: 'c'
		);
		$drawer->render();
	}
}
