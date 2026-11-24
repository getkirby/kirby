<?php

namespace Kirby\Panel\Response;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DrawerResponse::class)]
class DrawerResponseTest extends TestCase
{
	public function testKey(): void
	{
		$response = new DrawerResponse();
		$this->assertSame('drawer', $response->key());
	}
}
