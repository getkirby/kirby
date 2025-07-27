<?php

namespace Kirby\Panel\Routes;

use Kirby\Panel\Controller\ViewController;
use PHPUnit\Framework\Attributes\CoversClass;

class TestViewController extends ViewController
{
	public function view(): array
	{
		return ['test'];
	}
}

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

	public function testParamsWithController(): void
	{
		$routes = new ViewRoutes($this->area, []);
		$params = $routes->params([
			'action' => TestViewController::class
		]);

		$view = $params['action']();
		$this->assertSame(['test'], $view);
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
