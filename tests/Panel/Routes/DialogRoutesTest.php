<?php

namespace Kirby\Panel\Routes;

use PHPUnit\Framework\Attributes\CoversClass;

class TestDialog
{
	public function load()
	{
		return [
			'component' => 'k-test-dialog',
		];
	}

	public function submit()
	{
		return 'success';
	}
}

class TestDialogWithFor extends TestDialog
{
	public static function for(): static
	{
		return new static();
	}
}

#[CoversClass(DialogRoutes::class)]
class DialogRoutesTest extends TestCase
{
	public function testToArray(): void
	{
		$routes = new DialogRoutes($this->area, []);
		$this->assertSame([], $routes->toArray());

		$routes = new DialogRoutes($this->area, [
			'foo' => [
				'load'   => fn () => null,
				'submit' => fn () => null
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(2, $routes);
		$this->assertSame('test', $routes[0]['area']);
		$this->assertSame('dialogs/foo', $routes[0]['pattern']);
		$this->assertSame('GET', $routes[0]['method']);
		$this->assertSame('test', $routes[1]['area']);
		$this->assertSame('dialogs/foo', $routes[1]['pattern']);
		$this->assertSame('POST', $routes[1]['method']);
	}

	public function testToArrayWithPattern(): void
	{
		$routes = new DialogRoutes($this->area, [
			'foo' => [
				'pattern' => 'foo/(:any)',
				'load'    => fn () => null,
				'submit'  => fn () => null
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(2, $routes);
		$this->assertSame('dialogs/foo/(:any)', $routes[0]['pattern']);
		$this->assertSame('dialogs/foo/(:any)', $routes[1]['pattern']);
	}

	public function testToArrayWithController(): void
	{
		$routes = new DialogRoutes($this->area, [
			'test' => [
				'controller' => fn () => new TestDialog()
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(2, $routes);
		$render = $routes[0]['action']();
		$submit = $routes[1]['action']();
		$this->assertSame('k-test-dialog', $render['component']);
		$this->assertSame('success', $submit);

		// with just the class name
		$routes = new DialogRoutes($this->area, [
			'test' => [
				'controller' => TestDialog::class
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(2, $routes);
		$render = $routes[0]['action']();
		$submit = $routes[1]['action']();
		$this->assertSame('k-test-dialog', $render['component']);
		$this->assertSame('success', $submit);

		// with just the class name and ::for() method
		$routes = new DialogRoutes($this->area, [
			'test' => [
				'controller' => TestDialogWithFor::class
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(2, $routes);
		$render = $routes[0]['action']();
		$submit = $routes[1]['action']();
		$this->assertSame('k-test-dialog', $render['component']);
		$this->assertSame('success', $submit);
	}

	public function testToArrayMissingCallbacks(): void
	{
		$routes = new DialogRoutes($this->area, [
			'foo' => []
		]);

		$routes = $routes->toArray();
		$this->assertCount(2, $routes);
		$this->assertSame('The load handler is missing', $routes[0]['action']());
		$this->assertSame('The submit handler is missing', $routes[1]['action']());
	}
}
