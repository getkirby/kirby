<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class DuplicateExceptionTest extends TestCase
{
	public function testDefaults(): void
	{
		$exception = new DuplicateException();
		$this->assertSame('error.duplicate', $exception->getKey());
		$this->assertSame('The entry exists', $exception->getMessage());
		$this->assertSame(400, $exception->getHttpCode());
	}
}
