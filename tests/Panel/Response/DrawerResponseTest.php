<?php

namespace Kirby\Panel\Response;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Response\DrawerResponse
 */
class DrawerResponseTest extends TestCase
{
	/**
	 * @covers ::key
	 */
	public function testKey()
	{
		$response = new DrawerResponse();
		$this->assertSame('drawer', $response->key());
	}
}
