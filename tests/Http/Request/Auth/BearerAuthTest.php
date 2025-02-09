<?php

namespace Kirby\Http\Request\Auth;

use Kirby\Http\Request\Auth;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
#[CoversClass(BearerAuth::class)]
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
