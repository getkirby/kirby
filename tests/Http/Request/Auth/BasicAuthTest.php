<?php

namespace Kirby\Http\Request\Auth;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Kirby\Http\Request\Auth
 * @covers \Kirby\Http\Request\Auth\BasicAuth
 */
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
