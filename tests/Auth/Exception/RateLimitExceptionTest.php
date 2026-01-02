<?php

namespace Kirby\Auth\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RateLimitException::class)]
class RateLimitExceptionTest extends TestCase
{
	public function testDefaults(): void
	{
		$exception = new RateLimitException();
		$this->assertSame('error.auth.limit', $exception->getKey());
		$this->assertSame('Rate limit exceeded', $exception->getMessage());
		$this->assertSame(403, $exception->getHttpCode());
	}
}
