<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Router
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
	 * @covers ::routes
	 */
	public function testRoutes()
	{
		$router = new Router([]);
		$routes = $router->routes();

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
