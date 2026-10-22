<?php

namespace Kirby\Panel;

use Exception;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Request::class)]
class RequestTest extends TestCase
{
	public function testResponse(): void
	{
		$response = Request::response(['foo' => 'bar']);

		$this->assertSame(200, $response->code());
		$this->assertSame(['foo' => 'bar'], json_decode($response->body(), true));
	}

	public function testResponseEmptyArray(): void
	{
		$response = Request::response([]);

		$this->assertSame(200, $response->code());
		$this->assertSame([], json_decode($response->body(), true));
	}

	public function testResponseNoArray(): void
	{
		$response = Request::response('foo');
		$this->assertSame(500, $response->code());
	}

	public function testResponseThrowable(): void
	{
		$response = Request::response(new Exception());
		$this->assertSame(500, $response->code());
	}
}
