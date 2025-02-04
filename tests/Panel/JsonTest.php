<?php

namespace Kirby\Panel;

use Exception;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Json
 */
class JsonTest extends TestCase
{
	/**
	 * @covers ::response
	 */
	public function testResponseEmptyArray()
	{
		$response = Json::response([]);
		$this->assertSame(404, $response->code());
	}

	/**
	 * @covers ::response
	 */
	public function testResponseRedirect()
	{
		$redirect = new Redirect('https://getkirby.com');
		$response = Json::response($redirect);
		$body     = json_decode($response->body(), true);

		$this->assertSame(200, $response->code());
		$this->assertSame(200, $body['response']['code']);
		$this->assertSame('https://getkirby.com', $body['response']['redirect']);
	}

	/**
	 * @covers ::response
	 */
	public function testResponseThrowable()
	{
		$data     = new Exception();
		$response = Json::response($data);
		$this->assertSame(500, $response->code());
	}

	/**
	 * @covers ::response
	 */
	public function testResponseNoArray()
	{
		$data     = 'foo';
		$response = Json::response($data);
		$this->assertSame(500, $response->code());
	}
}
