<?php

namespace Kirby\Panel\Response;

use Kirby\Data\Json;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Response\RequestResponse
 */
class RequestResponseTest extends TestCase
{
	/**
	 * @covers ::body
	 */
	public function testBody()
	{
		$response = new RequestResponse($data = ['foo' => 'bar']);
		$this->assertSame(Json::encode($data), $response->body());
	}

	/**
	 * @covers ::data
	 */
	public function testData()
	{
		$response = new RequestResponse($data = ['foo' => 'bar']);
		$this->assertSame($data, $response->data());
	}
}
