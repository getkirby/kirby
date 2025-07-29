<?php

namespace Kirby\Panel\Routes;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Controller\DialogController;
use PHPUnit\Framework\Attributes\CoversClass;

class TestDialogController extends DialogController
{
	public function load(): array
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

class TestDialogControllerWithFactory extends TestDialogController
{
	public static function factory(): static
	{
		return new static();
	}
}

#[CoversClass(DialogRoutes::class)]
class DialogRoutesTest extends TestCase
{
	public function testControllerWithInvalidClass(): void
	{
		$routes = new DialogRoutes($this->area, []);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid controller class "Closure" expected child of"Kirby\Panel\Controller\DialogController"');

		$routes->params([
			'action' => Closure::class
		]);
	}

	public function testParamsWithController(): void
	{
		$routes = new DialogRoutes($this->area, []);
		$params = $routes->params([
			'action' => TestDialogController::class
		]);

		$render = $params['load']();
		$submit = $params['submit']();
		$this->assertSame('k-test-dialog', $render['component']);
		$this->assertSame('success', $submit);
	}

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
