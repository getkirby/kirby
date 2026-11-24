<?php

namespace Kirby\Panel\Response;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DialogResponse::class)]
class DialogResponseTest extends TestCase
{
	public function testFromTrue(): void
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

	public function testKey(): void
	{
		$response = new DialogResponse();
		$this->assertSame('dialog', $response->key());
	}
}
