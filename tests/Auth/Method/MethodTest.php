<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Auth;
use Kirby\Auth\Method;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Method::class)]
class MethodTest extends TestCase
{
	public function testisAvailable(): void
	{
		$auth = $this->createStub(Auth::class);
		$this->assertTrue(Method::isAvailable($auth));
	}

	public function testIsUsingChallenges(): void
	{
		$auth = $this->createStub(Auth::class);
		$this->assertFalse(Method::isUsingChallenges($auth));
	}
}
