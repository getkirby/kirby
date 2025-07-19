<?php

namespace Kirby\Panel\Routes;

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


#[CoversClass(Routes::class)]
class RoutesTest extends TestCase
{
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
			'type'    => 'route',
			'area'    => 'test',
			'method'  => 'GET',
			'action'  => $action = fn () => null
		], $routes->route('foo', $action));
	}
}
