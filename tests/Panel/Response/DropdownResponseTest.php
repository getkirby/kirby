<?php

namespace Kirby\Panel\Response;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Response\DropdownResponse
 */
class DropdownResponseTest extends TestCase
{
	/**
	 * @covers ::from
	 */
	public function testFrom()
	{
		$response = DropdownResponse::from($options = [
			[
				'text' => 'a'
			],
			[
				'text' => 'b'
			],
			[
				'text' => 'c'
			]
		]);

		$this->assertSame($options, $response->data()['options']);
	}

	/**
	 * @covers ::key
	 */
	public function testKey()
	{
		$response = new DropdownResponse();
		$this->assertSame('dropdown', $response->key());
	}
}
