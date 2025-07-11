<?php

namespace Kirby\Panel\Response;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Response\DialogResponse
 */
class DialogResponseTest extends TestCase
{
	/**
	 * @covers ::from
	 */
	public function testFromTrue()
	{
		$response = DialogResponse::from(true);
		$expected = [
			'code' => 200,
			'path' => null,
			'query' => [],
			'referrer' => '/'
		];

		$this->assertSame(200, $response->code());
		$this->assertSame($expected, $response->data());
	}

	/**
	 * @covers ::key
	 */
	public function testKey()
	{
		$response = new DialogResponse();
		$this->assertSame('dialog', $response->key());
	}
}
