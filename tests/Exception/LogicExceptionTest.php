<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

class LogicExceptionTest extends TestCase
{
	#[CoversNothing]
	public function testDefaults(): void
	{
		$exception = new LogicException();
		$this->assertSame('error.logic', $exception->getKey());
		$this->assertSame('This task cannot be finished', $exception->getMessage());
		$this->assertSame(400, $exception->getHttpCode());
	}
}
