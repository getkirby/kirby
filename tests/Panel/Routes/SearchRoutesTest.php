<?php

namespace Kirby\Panel\Routes;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SearchRoutes::class)]
class SearchRoutesTest extends TestCase
{
	public function testToArray(): void
	{
		$routes = new SearchRoutes($this->area, []);
		$this->assertSame([], $routes->toArray());

		$routes = new SearchRoutes($this->area, [
			'foo' => [
				'query' => fn (string $query, int $limit, int $page) => []
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(1, $routes);
		$this->assertSame('test', $routes[0]['area']);
		$this->assertSame('search/foo', $routes[0]['pattern']);
		$this->assertSame('GET', $routes[0]['method']);
	}
}
