<?php

namespace Kirby\Panel\Routes;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ViewRoutes::class)]
class ViewRoutesTest extends TestCase
{
	public function testIsAccessible(): void
	{
		$routes = new ViewRoutes($this->area, []);
		$this->assertTrue($routes->isAccessible([]));
		$this->assertFalse($routes->isAccessible([
			'when' => fn ($view, $area) => false
		]));
	}

	public function testToArray(): void
	{
		$routes = new ViewRoutes($this->area, [
			'foo' => [
				'pattern' => 'foo/(:any)',
				'action'  => fn (string $path) => null
			],
			'bar' => [
				'pattern' => 'bar/(:any)',
				'action'  => fn (string $path) => null,
				'when'    => fn ($view, $area) => false
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(1, $routes);
		$this->assertSame('foo/(:any)', $routes[0]['pattern']);
		$this->assertSame('test', $routes[0]['area']);
		$this->assertSame('view', $routes[0]['type']);
	}
}
