<?php

namespace Kirby\Panel\Routes;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DropdownRoutes::class)]
class DropdownRoutesTest extends TestCase
{
	public function testParams(): void
	{
		$routes = new DropdownRoutes($this->area, []);

		$params = $routes->params([
			'action' => fn () => 'a'
		]);
		$this->assertSame('a', $params['action']());

		$params = $routes->params([
			'options' => fn () => 'b'
		]);
		$this->assertSame('b', $params['action']());

		$params = $routes->params(fn () => 'c');
		$this->assertSame('c', $params['action']());
	}

	public function testToArray(): void
	{
		$routes = new DropdownRoutes($this->area, []);
		$this->assertSame([], $routes->toArray());

		$routes = new DropdownRoutes($this->area, [
			'foo' => [
				'pattern' => 'foo/(:any)',
				'action'  => fn () => null
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(1, $routes);
		$this->assertSame('test', $routes[0]['area']);
		$this->assertSame('dropdowns/foo/(:any)', $routes[0]['pattern']);
		$this->assertSame('GET|POST', $routes[0]['method']);
	}
}
