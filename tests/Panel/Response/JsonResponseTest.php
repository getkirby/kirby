<?php

namespace Kirby\Panel\Response;

use Exception;
use Kirby\Data\Json;
use Kirby\Exception\Exception as KirbyException;
use Kirby\Panel\Redirect;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JsonResponse::class)]
class JsonResponseTest extends TestCase
{
	public function testConstruct(): void
	{
		$response = new JsonResponse();

		$this->assertSame(200, $response->code());
		$this->assertSame('application/json', $response->type());
	}

	public function testBody(): void
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

	public function testBodyWithPrettyPrinting(): void
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

	public function testData(): void
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

	public function testError(): void
	{
		$error = JsonResponse::error('Custom error');

		$this->assertSame(404, $error->code());
		$this->assertSame('Custom error', $error->data()['error']);
	}

	public function testErrorWithCustomCode(): void
	{
		$error = JsonResponse::error('Custom error', 403);

		$this->assertSame(403, $error->code());
		$this->assertSame('Custom error', $error->data()['error']);
	}

	public function testFromJsonResponse(): void
	{
		$input  = new JsonResponse(['foo' => 'bar']);
		$output = JsonResponse::from($input);

		$this->assertSame($input, $output);
	}

	public function testFromRedirect(): void
	{
		$input  = new Redirect('https://getkirby.com');
		$output = JsonResponse::from($input);

		$this->assertSame('https://getkirby.com', $output->data()['redirect']);
	}

	public function testFromKirbyException(): void
	{
		$input  = new KirbyException('Error message');
		$output = JsonResponse::from($input);

		$this->assertSame('Error message', $output->data()['error']);
	}

	public function testFromException(): void
	{
		$input  = new Exception('Error message');
		$output = JsonResponse::from($input);

		$this->assertSame('Error message', $output->data()['error']);
	}

	public function testFromString(): void
	{
		$input  = 'test';
		$output = JsonResponse::from($input);

		$this->assertSame(500, $output->code());
		$this->assertSame('test', $output->data()['error']);
	}

	public function testFromArray(): void
	{
		$input  = ['foo' => 'bar'];
		$output = JsonResponse::from($input);

		$this->assertSame('bar', $output->data()['foo']);
	}

	public function testFromEmptyArray(): void
	{
		$input  = [];
		$output = JsonResponse::from($input);

		$this->assertSame(404, $output->code());
		$this->assertSame('The response is empty', $output->data()['error']);
	}

	public function testFromNull(): void
	{
		$output = JsonResponse::from(null);

		$this->assertSame(404, $output->code());
		$this->assertSame('The data could not be found', $output->data()['error']);
	}

	public function testFromInvalid(): void
	{
		$output = JsonResponse::from(5);

		$this->assertSame(500, $output->code());
		$this->assertSame('Invalid response', $output->data()['error']);
	}

	public function testHeaders(): void
	{
		$response = new JsonResponse();
		$expected = [
			'X-Panel'       => 'true',
			'Cache-Control' => 'no-store, private'
		];

		$this->assertSame($expected, $response->headers());
	}

	public function testKey(): void
	{
		$response = new JsonResponse();
		$this->assertSame('response', $response->key());
	}

	public function testPretty(): void
	{
		$response = new JsonResponse(pretty: true);

		$this->assertTrue($response->pretty());
	}

	public function testPrettyDefault(): void
	{
		$response = new JsonResponse();

		$this->assertFalse($response->pretty());
	}

	public function testPrettyFromQuery(): void
	{
		$response = new JsonResponse();

		$response->context(query: [
			'_pretty' => true
		]);

		$this->assertTrue($response->pretty());
	}

	public function testType(): void
	{
		$response = new JsonResponse();
		$this->assertSame('application/json', $response->type());
	}
}
