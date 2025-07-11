<?php

namespace Kirby\Panel\Response;

use Exception;
use Kirby\Cms\App;
use Kirby\Data\Json;
use Kirby\Exception\Exception as KirbyException;
use Kirby\Panel\Redirect;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Response\JsonResponse
 */
class JsonResponseTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$response = new JsonResponse();

		$this->assertSame(200, $response->code());
		$this->assertSame('application/json', $response->type());
	}

	/**
	 * @covers ::body
	 */
	public function testBody()
	{
		$response = new JsonResponse(['foo' => 'bar']);
		$expected = [
			'code'     => 200,
			'path'     => null,
			'query'    => [],
			'referrer' => '/',
			'foo'      => 'bar'
		];

		$this->assertSame(Json::encode(['response' => $expected]), $response->body());
	}

	/**
	 * @covers ::body
	 */
	public function testBodyWithPrettyPrinting()
	{
		$response = new JsonResponse(
			data: ['foo' => 'bar'],
			pretty: true
		);

		$expected = [
			'code'     => 200,
			'path'     => null,
			'query'    => [],
			'referrer' => '/',
			'foo'      => 'bar'
		];

		$this->assertSame(Json::encode(['response' => $expected], true), $response->body());
	}

	/**
	 * @covers ::data
	 */
	public function testData()
	{
		$response = new JsonResponse(['foo' => 'bar']);
		$expected = [
			'code'     => 200,
			'path'     => null,
			'query'    => [],
			'referrer' => '/',
			'foo'      => 'bar'
		];

		$this->assertSame($expected, $response->data());
	}

	/**
	 * @covers ::error
	 */
	public function testError()
	{
		$error = JsonResponse::error('Custom error');

		$this->assertSame(404, $error->code());
		$this->assertSame('Custom error', $error->data()['error']);
	}

	/**
	 * @covers ::error
	 */
	public function testErrorWithCustomCode()
	{
		$error = JsonResponse::error('Custom error', 403);

		$this->assertSame(403, $error->code());
		$this->assertSame('Custom error', $error->data()['error']);
	}

	/**
	 * @covers ::from
	 */
	public function testFromJsonResponse()
	{
		$input  = new JsonResponse(['foo' => 'bar']);
		$output = JsonResponse::from($input);

		$this->assertSame($input, $output);
	}

	/**
	 * @covers ::from
	 */
	public function testFromRedirect()
	{
		$input  = new Redirect('https://getkirby.com');
		$output = JsonResponse::from($input);

		$this->assertSame('https://getkirby.com', $output->data()['redirect']);
	}

	/**
	 * @covers ::from
	 */
	public function testFromKirbyException()
	{
		$input  = new KirbyException('Error message');
		$output = JsonResponse::from($input);

		$this->assertSame('Error message', $output->data()['error']);
	}

	/**
	 * @covers ::from
	 */
	public function testFromException()
	{
		$input  = new Exception('Error message');
		$output = JsonResponse::from($input);

		$this->assertSame('Error message', $output->data()['error']);
	}

	/**
	 * @covers ::from
	 */
	public function testFromString()
	{
		$input  = 'test';
		$output = JsonResponse::from($input);

		$this->assertSame(500, $output->code());
		$this->assertSame('test', $output->data()['error']);
	}

	/**
	 * @covers ::from
	 */
	public function testFromArray()
	{
		$input  = ['foo' => 'bar'];
		$output = JsonResponse::from($input);

		$this->assertSame('bar', $output->data()['foo']);
	}

	/**
	 * @covers ::from
	 */
	public function testFromEmptyArray()
	{
		$input  = [];
		$output = JsonResponse::from($input);

		$this->assertSame(404, $output->code());
		$this->assertSame('The response is empty', $output->data()['error']);
	}

	/**
	 * @covers ::headers
	 */
	public function testHeaders()
	{
		$response = new JsonResponse();
		$expected = [
			'X-Fiber'       => 'true',
			'Cache-Control' => 'no-store, private'
		];

		$this->assertSame($expected, $response->headers());
	}

	/**
	 * @covers ::key
	 */
	public function testKey()
	{
		$response = new JsonResponse();
		$this->assertSame('response', $response->key());
	}

	/**
	 * @covers ::pretty
	 */
	public function testPretty()
	{
		$response = new JsonResponse(pretty: true);

		$this->assertTrue($response->pretty());
	}

	/**
	 * @covers ::pretty
	 */
	public function testPrettyDefault()
	{
		$response = new JsonResponse();

		$this->assertFalse($response->pretty());
	}

	/**
	 * @covers ::pretty
	 */
	public function testPrettyFromQuery()
	{
		$response = new JsonResponse();

		$response->context(query: [
			'_pretty' => true
		]);

		$this->assertTrue($response->pretty());
	}

	/**
	 * @covers ::type
	 */
	public function testType()
	{
		$response = new JsonResponse();
		$this->assertSame('application/json', $response->type());
	}
}
