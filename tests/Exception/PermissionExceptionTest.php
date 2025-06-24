<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

class PermissionExceptionTest extends TestCase
{
	#[CoversNothing]
	public function testDefaults(): void
	{
		$exception = new PermissionException();
		$this->assertSame('error.permission', $exception->getKey());
		$this->assertSame('You are not allowed to do this', $exception->getMessage());
		$this->assertSame(403, $exception->getHttpCode());
	}
}
