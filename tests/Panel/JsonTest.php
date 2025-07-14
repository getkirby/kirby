<?php

namespace Kirby\Panel;

use Exception;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Json::class)]
class JsonTest extends TestCase
{
	public function testResponseEmptyArray(): void
	{
		$response = Json::response([]);
		$this->assertSame(404, $response->code());
	}

	public function testResponseRedirect(): void
	{
		$redirect = new Redirect('https://getkirby.com');
		$response = Json::response($redirect);
		$body     = json_decode($response->body(), true);

		$this->assertSame(200, $response->code());
		$this->assertSame(200, $body['response']['code']);
		$this->assertSame('https://getkirby.com', $body['response']['redirect']);
	}

	public function testResponseThrowable(): void
	{
		$data     = new Exception();
		$response = Json::response($data);
		$this->assertSame(500, $response->code());
	}

	public function testResponseNoArray(): void
	{
		$data     = 'foo';
		$response = Json::response($data);
		$this->assertSame(500, $response->code());
	}
}
