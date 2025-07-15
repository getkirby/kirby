<?php

namespace Kirby\Panel\Routes;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RequestRoutes::class)]
class RequestRoutesTest extends TestCase
{
	public function testToArray(): void
	{
		$routes = new RequestRoutes($this->area, []);
		$this->assertSame([], $routes->toArray());

		$routes = new RequestRoutes($this->area, [
			'foo' => [
				'pattern' => 'foo/(:any)',
				'action'  => fn () => null
			]
		]);

		$routes = $routes->toArray();
		$this->assertCount(1, $routes);
		$this->assertSame('test', $routes[0]['area']);
		$this->assertSame('foo/(:any)', $routes[0]['pattern']);
		$this->assertSame('GET', $routes[0]['method']);
	}
}
