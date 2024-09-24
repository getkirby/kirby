<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Dialog
 */
class DialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Dialog';

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
		$error = Dialog::error('Test');

		$this->assertSame(404, $error['code']);
		$this->assertSame('Test', $error['error']);

		// custom code
		$error = Dialog::error('Test', 500);

		$this->assertSame(500, $error['code']);
		$this->assertSame('Test', $error['error']);
	}

	/**
	 * @covers ::response
	 */
	public function testResponse(): void
	{
		$response = Dialog::response([
			'test' => 'Test'
		]);

		$expected = [
			'$dialog' => [
				'test'     => 'Test',
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
	public function testResponseFromTrue(): void
	{
		$response = Dialog::response(true);
		$expected = [
			'$dialog' => [
				'code'     => 200,
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
	public function testResponseFromInvalidData(): void
	{
		$response = Dialog::response(1234);
		$expected = [
			'$dialog' => [
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
		$response  = Dialog::response($exception);
		$expected  = [
			'$dialog' => [
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
		$response  = Dialog::response($exception);
		$expected  = [
			'$dialog' => [
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
		$area = [
			'load'   => $load   = function () {
			},
			'submit' => $submit = function () {
			},
		];

		$routes = Dialog::routes(
			id: 'test',
			areaId: 'test',
			prefix: 'dialogs',
			options: $area
		);

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
	 * @covers ::routes
	 */
	public function testRoutesWithoutHandlers(): void
	{
		$area = [
			'dialogs' => [
				'test' => []
			]
		];

		$routes = Dialog::routes(
			id: 'test',
			areaId: 'test',
			options: []
		);

		$this->assertSame('The load handler is missing', $routes[0]['action']());
		$this->assertSame('The submit handler is missing', $routes[1]['action']());
	}
}
