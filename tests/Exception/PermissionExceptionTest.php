<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class PermissionExceptionTest extends TestCase
{
	public function testDefaults()
	{
		$exception = new PermissionException();
		$this->assertSame('error.permission', $exception->getKey());
		$this->assertSame('You are not allowed to do this', $exception->getMessage());
		$this->assertSame(403, $exception->getHttpCode());
	}
}
