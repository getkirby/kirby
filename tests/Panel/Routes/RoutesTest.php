<?php

namespace Kirby\Panel\Routes;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Controller\Controller;
use PHPUnit\Framework\Attributes\CoversClass;

class TestRoutes extends Routes
{
	public function toArray(): array
	{
		return [];
	}
}

class TestRoutesWithPrefix extends Routes
{
	protected static string $prefix = 'test';

	public function toArray(): array
	{
		return [];
	}
}

class TestController extends Controller
{
	public function load(): array
	{
		return ['bar'];
	}
}

class TestControllerWithFactory extends Controller
{
	public function load(): array
	{
		return ['factory'];
	}

	public static function factory(): static
	{
		return new static();
	}
}

#[CoversClass(Routes::class)]
class RoutesTest extends TestCase
{
	public function testController(): void
	{
		$routes = new TestRoutes($this->area, []);
		$params = $routes->controller([]);
		$this->assertArrayNotHasKey('action', $params);
		$this->assertArrayNotHasKey('load', $params);
		$this->assertArrayNotHasKey('submit', $params);
	}

	public function testControllerWithArray()
	{
		$routes = new TestRoutes($this->area, []);
		$params = $routes->controller([
			'action' => [
				'component' => 'k-test',
			],
		]);

		$this->assertSame('k-test', $params['action']['component']);
		$this->assertSame('k-test', $params['load']()['component']);
		$this->assertTrue($params['submit']());
	}

	public function testControllerWithArrayFromClosure(): void
	{
		$routes = new TestRoutes($this->area, []);
		$params = $routes->controller([
			'action' => fn () => ['component' => 'k-test'],
		]);

		$this->assertSame('k-test', $params['action']()['component']);
		$this->assertSame('k-test', $params['load']()['component']);
		$this->assertTrue($params['submit']());
	}

	public function testControllerWithClassname(): void
	{
		$routes = new TestRoutes($this->area, []);
		$params = $routes->controller([
			'action' => TestController::class,
		]);

		$this->assertSame(['bar'], $params['load']());
		$this->assertTrue($params['submit']());
	}

	public function testControllerWithClassnameFactory(): void
	{
		$routes = new TestRoutes($this->area, []);
		$params = $routes->controller([
			'action' => TestControllerWithFactory::class,
		]);

		$this->assertSame(['factory'], $params['load']());
		$this->assertTrue($params['submit']());
	}

	public function testControllerWithInvalidClassname(): void
	{
		$routes = new TestRoutes($this->area, []);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid controller class "Closure" expected child of"Kirby\Panel\Controller\Controller"');

		$routes->controller([
			'action' => Closure::class
		]);
	}

	public function testControllerWithControllerObject(): void
	{
		$routes = new TestRoutes($this->area, []);
		$params = $routes->controller([
			'action' => new TestController(),
		]);

		$this->assertSame(['bar'], $params['load']());
		$this->assertTrue($params['submit']());
	}

	public function testControllerWithControllerObjectFromClosure(): void
	{
		$routes = new TestRoutes($this->area, []);
		$params = $routes->controller([
			'action' => fn () => new TestController(),
		]);

		$this->assertSame(['bar'], $params['load']());
		$this->assertTrue($params['submit']());
	}

	public function testParams(): void
	{
		$routes = new TestRoutes($this->area, []);
		$params = $routes->params(fn () => 'test');
		$this->assertSame('test', $params['action']());

		$params = $routes->params(
			params: ['query' => fn () => 'test'],
			action: 'query'
		);
		$this->assertSame('test', $params['action']());
	}

	public function testPattern(): void
	{
		$routes = new TestRoutes($this->area, []);
		$this->assertSame('foo', $routes->pattern('foo/'));
		$this->assertSame(['foo', 'bar'], $routes->pattern(['foo/', '/bar']));

		$routes = new TestRoutesWithPrefix($this->area, []);
		$this->assertSame('test/foo', $routes->pattern('foo/'));
	}

	public function testRoute(): void
	{
		$routes = new TestRoutes($this->area, []);
		$this->assertSame([
			'auth'    => true,
			'pattern' => 'foo',
			'type'    => '',
			'area'    => 'test',
			'method'  => 'GET',
			'action'  => $action = fn () => null
		], $routes->route('foo', $action));
	}
}
