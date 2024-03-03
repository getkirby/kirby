<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Http\Response;
use Kirby\TestCase;

class AppIoTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function app()
	{
		return new App([
			'roots' => [
				'index'     => '/dev/null',
				'templates' => static::FIXTURES . '/AppIoTest/templates'
			]
		]);
	}

	public function testException()
	{
		$response = $this->app()->io(new Exception([
			'fallback' => 'Nope',
			'httpCode' => 501
		]));

		$this->assertSame(501, $response->code());
		$this->assertSame('Nope', $response->body());
	}

	public function testExceptionErrorPage()
	{
		$app = $this->app()->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'error',
						'template' => 'error'
					]
				]
			]
		]);

		$response = $app->io(new Exception('Nope'));

		$this->assertSame(500, $response->code());
		$this->assertSame('Error: Nope', $response->body());
	}

	public function testExceptionWithInvalidHttpCode()
	{
		$response = $this->app()->io(new \Exception('Nope', 8000));

		$this->assertSame(500, $response->code());
		$this->assertSame('Nope', $response->body());
	}

	public function testEmpty()
	{
		$response = $this->app()->io('');

		$this->assertSame(404, $response->code());
		$this->assertSame('Not found', $response->body());
	}

	public function testResponder()
	{
		$app   = $this->app();
		$input = $app->response()->code(201)->body('Test');

		$response = $app->io($input);

		$this->assertSame(201, $response->code());
		$this->assertSame('Test', $response->body());
	}

	public function testResponse()
	{
		$input = new Response([
			'code' => 200,
			'body' => 'Test',
			'type' => 'text/plain',
			'headers' => [
				'X-Foo'  => 'Foobar',
				'X-Test' => 'Test'
			]
		]);

		$app = $this->app();
		$app->response()->header('Cache-Control', 'no-cache');
		$app->response()->header('X-Foo', 'Bar');
		$response = $app->io($input);

		$this->assertSame([
			'type' => 'text/plain',
			'charset' => 'UTF-8',
			'code' => 200,
			'headers' => [
				'Cache-Control' => 'no-cache',
				'X-Foo'         => 'Foobar',
				'X-Test'        => 'Test'
			],
			'body' => 'Test',
		], $response->toArray());
	}

	public function testPage()
	{
		$input = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$response = $this->app()->io($input);

		$this->assertSame(200, $response->code());
		$this->assertSame('Test template', $response->body());
	}

	public function testPageErrorPageException()
	{
		$input = new Page([
			'slug'     => 'test',
			'template' => 'errorpage-exception'
		]);

		$response = $this->app()->io($input);

		$this->assertSame(403, $response->code());
		$this->assertSame('Exception message', $response->body());
	}

	public function testPageErrorPageExceptionErrorPage()
	{
		$app = $this->app()->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'error',
						'template' => 'error'
					]
				]
			]
		]);

		$input = new Page([
			'slug'     => 'test',
			'template' => 'errorpage-exception'
		]);

		$response = $app->io($input);

		$this->assertSame(403, $response->code());
		$this->assertSame('Error: Exception message', $response->body());
	}

	public function testString()
	{
		$response = $this->app()->io('Test');

		$this->assertSame(200, $response->code());
		$this->assertSame('Test', $response->body());
	}

	public function testArray()
	{
		$response = $this->app()->io($array = ['foo' => 'bar']);

		$this->assertSame(200, $response->code());
		$this->assertSame(json_encode($array), $response->body());
	}
}
