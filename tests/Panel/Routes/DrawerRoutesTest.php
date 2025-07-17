<?php

namespace Kirby\Panel\Routes;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DrawerRoutes::class)]
class DrawerRoutesTest extends TestCase
{
	public function testToArray(): void
	{
		$routes = new DrawerRoutes($this->area, [
			'foo' => [
				'load'   => fn () => null,
				'submit' => fn () => null
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(2, $routes);
		$this->assertSame('test', $routes[0]['area']);
		$this->assertSame('drawers/foo', $routes[0]['pattern']);
		$this->assertSame('test', $routes[1]['area']);
		$this->assertSame('drawers/foo', $routes[1]['pattern']);
	}
}
