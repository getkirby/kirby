<?php

namespace Kirby\Http\Request\Auth;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Kirby\Http\Request\Auth
 * @covers \Kirby\Http\Request\Auth\BearerAuth
 */
class BearerAuthTest extends TestCase
{
	public function testInstance()
	{
		$auth = new BearerAuth('abcd');

		$this->assertSame('bearer', $auth->type());
		$this->assertSame('abcd', $auth->data());
		$this->assertSame('abcd', $auth->token());
		$this->assertSame('Bearer abcd', (string)$auth);
	}
}
