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
	public function foo(): string
	{
		return 'bar';
	}
}

class TestControllerWithFactory extends Controller
{
	public static function factory(): string
	{
		return 'factory';
	}
}

#[CoversClass(Routes::class)]
class RoutesTest extends TestCase
{
	public function testController(): void
	{
		$routes = new TestRoutes($this->area, []);
		$this->assertNull($routes->controller(null));

		$controller = $routes->controller(TestController::class);
		$this->assertSame('bar', $controller()->foo());

		$controller = $routes->controller(TestControllerWithFactory::class);
		$this->assertSame('factory', $controller());
	}

	public function testControllerWithInvalidClass(): void
	{
		$routes = new TestRoutes($this->area, []);
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid controller class "Closure" expected child of"Kirby\Panel\Controller\Controller"');
		$routes->controller(Closure::class);
	}

	public function testParams(): void
	{
		$routes = new TestRoutes($this->area, []);
		$params = $routes->params(fn () => 'test');
		$this->assertSame('test', $params['action']());
	}

	public function testPattern(): void
	{
		$routes = new TestRoutes($this->area, []);
		$this->assertSame('foo', $routes->pattern('foo/'));

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
