<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(View::class)]
class ViewTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.View';

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

	public function testError(): void
	{
		// without user
		$error = View::error('Test');

		$expected = [
			'code' => 404,
			'component' => 'k-error-view',
			'error' => 'Test',
			'props' => [
				'error' => 'Test',
				'layout' => 'outside'
			],
			'title' => 'Error'
		];

		$this->assertSame($expected, $error);

		// with user
		$this->app->impersonate('kirby');
		$error = View::error('Test');

		$this->assertSame('inside', $error['props']['layout']);

		// user without panel access
		$this->app->impersonate('nobody');
		$error = View::error('Test');

		$this->assertSame('outside', $error['props']['layout']);
	}

	public function testErrorWithCustomCode(): void
	{
		$error = View::error('Test', 403);
		$this->assertSame(403, $error['code']);
	}

	public function testResponseAsHTML(): void
	{
		// create panel dist files first to avoid redirect
		(new Assets())->link();

		// get panel response
		$response = View::response([
			'test' => 'Test'
		]);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(200, $response->code());
		$this->assertSame('text/html', $response->type());
		$this->assertSame('UTF-8', $response->charset());
		$this->assertNotNull($response->body());
	}

	public function testResponseAsJSON(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true
				]
			]
		]);

		// get panel response
		$response = View::response([
			'test' => 'Test'
		]);

		$this->assertSame('application/json', $response->type());
		$this->assertSame('true', $response->header('X-Panel'));
	}

	public function testResponseFromRedirect(): void
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		$redirect = new Redirect('https://getkirby.com');
		$response = View::response($redirect);

		$this->assertInstanceOf(Response::class, $response);

		$this->assertSame(302, $response->code());
		$this->assertSame('https://getkirby.com', $response->header('Location'));
	}

	public function testResponseFromKirbyException(): void
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		$exception = new NotFoundException(message: 'Test');
		$response  = View::response($exception);
		$json      = json_decode($response->body(), true);

		$this->assertSame(404, $response->code());
		$this->assertSame('k-error-view', $json['view']['component']);
		$this->assertSame('Test', $json['view']['props']['error']);
	}

	public function testResponseFromException(): void
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		$exception = new Exception('Test');
		$response  = View::response($exception);
		$json      = json_decode($response->body(), true);

		$this->assertSame(500, $response->code());
		$this->assertSame('k-error-view', $json['view']['component']);
		$this->assertSame('Test', $json['view']['props']['error']);
	}

	public function testResponseFromUnsupportedResult(): void
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		$response = View::response(1234);
		$json     = json_decode($response->body(), true);

		$this->assertSame(500, $response->code());
		$this->assertSame('k-error-view', $json['view']['component']);
		$this->assertSame('Invalid Panel response', $json['view']['props']['error']);
	}
}
