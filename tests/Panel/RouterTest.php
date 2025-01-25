<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Router
 * @covers ::__construct
 */
class RouterTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Panel';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::response
	 */
	public function testResponse()
	{
		$response = new Response('Test');
		$panel    = $this->app->panel();
		$router   = new Router($panel);

		// response objects should not be modified
		$this->assertSame($response, $router->response($response));
	}

	/**
	 * @covers ::response
	 */
	public function testResponseFromNullOrFalse()
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		// null is interpreted as 404
		$panel    = $this->app->panel();
		$router   = new Router($panel);
		$response = $router->response(null);
		$json     = json_decode($response->body(), true);

		$this->assertSame(404, $response->code());
		$this->assertSame('k-error-view', $json['view']['component']);
		$this->assertSame('The data could not be found', $json['view']['props']['error']);

		// false is interpreted as 404
		$response = $router->response(false);
		$json     = json_decode($response->body(), true);

		$this->assertSame(404, $response->code());
		$this->assertSame('k-error-view', $json['view']['component']);
		$this->assertSame('The data could not be found', $json['view']['props']['error']);
	}

	/**
	 * @covers ::response
	 */
	public function testResponseFromString()
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		// strings are interpreted as errors
		$panel    = $this->app->panel();
		$router   = new Router($panel);
		$response = $router->response('Test');
		$json     = json_decode($response->body(), true);

		$this->assertSame(500, $response->code());
		$this->assertSame('k-error-view', $json['view']['component']);
		$this->assertSame('Test', $json['view']['props']['error']);
	}

	/**
	 * @covers ::routes
	 */
	public function testRoutes()
	{
		$routes = Router::routes([]);

		$this->assertSame('browser', $routes[0]['pattern']);
		$this->assertSame(['/', 'installation', 'login'], $routes[1]['pattern']);
		$this->assertSame('(:all)', $routes[2]['pattern']);
		$this->assertSame('Could not find Panel view for route: foo', $routes[2]['action']('foo'));
	}


	/**
	 * @covers ::routesForDialogs
	 */
	public function testRoutesForDialogs(): void
	{
		$area = [
			'dialogs' => [
				'test' => [
					'load'   => $load   = function () {
					},
					'submit' => $submit = function () {
					},
				]
			]
		];

		$routes = Router::routesForDialogs('test', $area);

		$expected = [
			[
				'pattern' => 'dialogs/test',
				'type'    => 'dialog',
				'area'    => 'test',
				'action'  => $load,
			],
			[
				'pattern' => 'dialogs/test',
				'type'    => 'dialog',
				'area'    => 'test',
				'method'  => 'POST',
				'action'  => $submit,
			]
		];

		$this->assertSame($expected, $routes);
	}

	/**
	 * @covers ::routesForDialogs
	 */
	public function testRoutesForDialogsWithoutHandlers(): void
	{
		$area = [
			'dialogs' => [
				'test' => []
			]
		];

		$routes = Router::routesForDialogs('test', $area);

		$this->assertSame('The load handler is missing', $routes[0]['action']());
		$this->assertSame('The submit handler is missing', $routes[1]['action']());
	}

	/**
	 * @covers ::routesForDrawers
	 */
	public function testRoutesForDrawers(): void
	{
		$area = [
			'drawers' => [
				'test' => [
					'load'   => $load   = function () {
					},
					'submit' => $submit = function () {
					},
				]
			]
		];

		$routes = Router::routesForDrawers('test', $area);

		$expected = [
			[
				'pattern' => 'drawers/test',
				'type'    => 'drawer',
				'area'    => 'test',
				'action'  => $load,
			],
			[
				'pattern' => 'drawers/test',
				'type'    => 'drawer',
				'area'    => 'test',
				'method'  => 'POST',
				'action'  => $submit,
			]
		];

		$this->assertSame($expected, $routes);
	}

	/**
	 * @covers ::routesForDropdowns
	 */
	public function testRoutesForDropdowns(): void
	{
		$area = [
			'dropdowns' => [
				'test' => [
					'pattern' => 'test',
					'action'  => $action = fn () => [
						[
							'text' => 'Test',
							'link' => '/test'
						]
					]
				]
			]
		];

		$routes = Router::routesForDropdowns('test', $area);

		$expected = [
			[
				'pattern' => 'dropdowns/test',
				'type'    => 'dropdown',
				'area'    => 'test',
				'method'  => 'GET|POST',
				'action'  => $action,
			]
		];

		$this->assertSame($expected, $routes);
	}

	/**
	 * @covers ::routesForDropdowns
	 */
	public function testRoutesForDropdownsWithOptions(): void
	{
		$area = [
			'dropdowns' => [
				'test' => [
					'pattern' => 'test',
					'options' => $action = fn () => [
						[
							'text' => 'Test',
							'link' => '/test'
						]
					]
				]
			]
		];

		$routes = Router::routesForDropdowns('test', $area);

		$expected = [
			[
				'pattern' => 'dropdowns/test',
				'type'    => 'dropdown',
				'area'    => 'test',
				'method'  => 'GET|POST',
				'action'  => $action,
			]
		];

		$this->assertSame($expected, $routes);
	}

	/**
	 * @covers ::routesForDropdowns
	 */
	public function testRoutesForDropdownsWithShortcut(): void
	{
		$area = [
			'dropdowns' => [
				'test' => $action = fn () => [
					[
						'text' => 'Test',
						'link' => '/test'
					]
				]
			]
		];

		$routes = Router::routesForDropdowns('test', $area);

		$expected = [
			[
				'pattern' => 'dropdowns/test',
				'type'    => 'dropdown',
				'area'    => 'test',
				'method'  => 'GET|POST',
				'action'  => $action,
			]
		];

		$this->assertSame($expected, $routes);
	}

	/**
	 * @covers ::routesForViews
	 */
	public function testRoutesForViews(): void
	{
		$area = [
			'views' => [
				[
					'pattern' => 'test',
					'action'  => $callback = function () {
					}
				]
			]
		];

		$routes = Router::routesForViews('test', $area);

		$expected = [
			[
				'pattern' => 'test',
				'action'  => $callback,
				'area'    => 'test',
				'type'    => 'view'
			]
		];

		$this->assertSame($expected, $routes);
	}
}
