<?php

namespace Kirby\Http\Request\Auth;

use Kirby\Http\Request\Auth;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
#[CoversClass(BasicAuth::class)]
class BasicAuthTest extends TestCase
{
	public function testInstance()
	{
		$auth = new BasicAuth($data = base64_encode($credentials = 'testuser:testpass'));

		$this->assertSame('basic', $auth->type());
		$this->assertSame($data, $auth->data());
		$this->assertSame($credentials, $auth->credentials());
		$this->assertSame('testpass', $auth->password());
		$this->assertSame('testuser', $auth->username());
		$this->assertSame('Basic ' . $data, (string)$auth);
	}
}
