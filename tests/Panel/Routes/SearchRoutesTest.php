<?php

namespace Kirby\Panel\Routes;

use Kirby\Panel\Controller\SearchController;
use PHPUnit\Framework\Attributes\CoversClass;

class TestSearchController extends SearchController
{
	public function results(): array
	{
		return ['a', 'b', 'c'];
	}
}

#[CoversClass(SearchRoutes::class)]
class SearchRoutesTest extends TestCase
{
	public function testParamsWithController(): void
	{
		$routes = new SearchRoutes($this->area, []);
		$params = $routes->params([
			'action' => TestSearchController::class
		]);

		$options = $params['action']();
		$this->assertSame(['a', 'b', 'c'], $options);
	}

	public function testToArray(): void
	{
		$routes = new SearchRoutes($this->area, []);
		$this->assertSame([], $routes->toArray());

		$routes = new SearchRoutes($this->area, [
			'foo' => [
				'query' => fn (string $query, int $limit, int $page) => ['test']
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(1, $routes);
		$this->assertSame('test', $routes[0]['area']);
		$this->assertSame('search/foo', $routes[0]['pattern']);
		$this->assertSame(['test'], $routes[0]['action']());
		$this->assertSame('GET', $routes[0]['method']);
	}
}
