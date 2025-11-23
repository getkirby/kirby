<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class ErrorPageExceptionTest extends TestCase
{
	public function testDefaults(): void
	{
		$exception = new ErrorPageException();
		$this->assertSame('error.errorPage', $exception->getKey());
		$this->assertSame('Triggered error page', $exception->getMessage());
		$this->assertSame(404, $exception->getHttpCode());
	}
}
