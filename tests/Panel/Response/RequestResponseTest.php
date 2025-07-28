<?php

namespace Kirby\Panel\Response;

use Kirby\Data\Json;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RequestResponse::class)]
class RequestResponseTest extends TestCase
{
	public function testBody(): void
	{
		$response = new RequestResponse($data = ['foo' => 'bar']);
		$this->assertSame(Json::encode($data), $response->body());
	}

	public function testData(): void
	{
		$response = new RequestResponse($data = ['foo' => 'bar']);
		$this->assertSame($data, $response->data());
	}
}
