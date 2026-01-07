<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Cms\Auth;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Method::class)]
class MethodTest extends TestCase
{
	public function testIsEnabled(): void
	{
		$auth = $this->createStub(Auth::class);
		$this->assertTrue(Method::isEnabled($auth));
	}

	public function testIsUsingChallenges(): void
	{
		$auth = $this->createStub(Auth::class);
		$this->assertFalse(Method::isUsingChallenges($auth));
	}
}
