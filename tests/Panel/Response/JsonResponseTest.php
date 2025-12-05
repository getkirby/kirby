<?php

namespace Kirby\Panel\Response;

use Exception;
use Kirby\Data\Json;
use Kirby\Exception\Exception as KirbyException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Panel\Redirect;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button;
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

	public function testErrorWithDetails(): void
	{
		$response = JsonResponse::error('Test', 404, $details = [
			'test' => [
				'label'   => 'Label',
				'message' => 'Message'
			]
		]);

		$this->assertSame(404, $response->code());
		$this->assertSame('Test', $response->data()['error']);
		$this->assertSame($details, $response->data()['details']);
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
		$this->expectException(KirbyException::class);
		$this->expectExceptionMessage('Error message');

		JsonResponse::from(new KirbyException('Error message'));
	}

	public function testFromException(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Error message');

		JsonResponse::from(new Exception('Error message'));
	}

	public function testFromUiComponent(): void
	{
		$input  = new Button('k-my-button');
		$output = JsonResponse::from($input);

		$this->assertSame('k-my-button', $output->data()['component']);
	}

	public function testFromString(): void
	{
		$this->expectException(KirbyException::class);
		$this->expectExceptionMessage('test');

		JsonResponse::from('test');
	}

	public function testFromArray(): void
	{
		$input  = ['foo' => 'bar'];
		$output = JsonResponse::from($input);

		$this->assertSame('bar', $output->data()['foo']);
	}

	public function testFromEmptyArray(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The response is empty');

		JsonResponse::from([]);
	}

	public function testFromNull(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The data could not be found');

		JsonResponse::from(null);
	}

	public function testFromInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid response');

		JsonResponse::from(5);
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
