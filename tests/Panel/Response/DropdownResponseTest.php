<?php

namespace Kirby\Panel\Response;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DropdownResponse::class)]
class DropdownResponseTest extends TestCase
{
	public function testFrom(): void
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

	public function testKey(): void
	{
		$response = new DropdownResponse();
		$this->assertSame('dropdown', $response->key());
	}
}
