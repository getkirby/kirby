<?php

namespace Kirby\Auth\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ChallengeTimeoutException::class)]
class ChallengeTimeoutExceptionTest extends TestCase
{
	public function testDefaults(): void
	{
		$exception = new ChallengeTimeoutException();

		$this->assertSame('Authentication challenge timeout', $exception->getMessage());
		$this->assertSame('error.permission', $exception->getKey());
		$this->assertSame(['challengeDestroyed' => true], $exception->getDetails());
		$this->assertSame(403, $exception->getHttpCode());
	}
}
