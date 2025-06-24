<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Dropdown
 */
class DropdownTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Dropdown';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);

		// clear fake json requests
		$_GET = [];

		// clean up $_SERVER
		unset($_SERVER['SERVER_SOFTWARE']);
	}

	/**
	 * @covers ::error
	 */
	public function testError(): void
	{
		// default
		$error = Dropdown::error('Test');

		$this->assertSame(404, $error['code']);
		$this->assertSame('Test', $error['error']);

		// custom code
		$error = Dropdown::error('Test', 500);

		$this->assertSame(500, $error['code']);
		$this->assertSame('Test', $error['error']);
	}

	/**
	 * @covers ::response
	 */
	public function testResponse(): void
	{
		$response = Dropdown::response([
			'test' => 'Test'
		]);

		$expected = [
			'$dropdown' => [
				'options'  => ['Test'],
				'code'     => 200,
				'path'     => null,
				'query'    => [],
				'referrer' => '/'
			]
		];

		$this->assertSame('application/json', $response->type());
		$this->assertSame('true', $response->header('X-Fiber'));
		$this->assertSame($expected, json_decode($response->body(), true));
	}

	/**
	 * @covers ::response
	 */
	public function testResponseFromInvalidData(): void
	{
		$response = Dropdown::response(1234);
		$expected = [
			'$dropdown' => [
				'code'     => 500,
				'error'    => 'Invalid response',
				'path'     => null,
				'query'    => [],
				'referrer' => '/'
			]
		];

		$this->assertSame($expected, json_decode($response->body(), true));
	}

	/**
	 * @covers ::response
	 */
	public function testResponseFromException(): void
	{
		$exception = new Exception('Test');
		$response  = Dropdown::response($exception);
		$expected  = [
			'$dropdown' => [
				'code'     => 500,
				'error'    => 'Test',
				'path'     => null,
				'query'    => [],
				'referrer' => '/'
			]
		];

		$this->assertSame($expected, json_decode($response->body(), true));
	}

	/**
	 * @covers ::response
	 */
	public function testResponseFromKirbyException(): void
	{
		$exception = new NotFoundException(message: 'Test');
		$response  = Dropdown::response($exception);
		$expected  = [
			'$dropdown' => [
				'code'     => 404,
				'error'    => 'Test',
				'path'     => null,
				'query'    => [],
				'referrer' => '/'
			]
		];

		$this->assertSame($expected, json_decode($response->body(), true));
	}

	/**
	 * @covers ::routes
	 */
	public function testRoutes(): void
	{
		$dropdown = [
			'pattern' => 'test',
			'action'  => $action = fn () => [
				[
					'text' => 'Test',
					'link' => '/test'
				]
			]
		];

		$routes = Dropdown::routes(
			id: 'test',
			areaId: 'test',
			prefix: 'dropdowns',
			options: $dropdown
		);

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
	 * @covers ::routes
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

		$routes = Panel::routesForDropdowns('test', $area);

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
	 * @covers ::routes
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

		$routes = Panel::routesForDropdowns('test', $area);

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
}
