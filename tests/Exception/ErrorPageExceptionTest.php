<?php

namespace Kirby\Exception;

use Kirby\TestCase;

class ErrorPageExceptionTest extends TestCase
{
	/**
	 * @coversNothing
	 */
	public function testDefaults()
	{
		$exception = new ErrorPageException();
		$this->assertSame('error.errorPage', $exception->getKey());
		$this->assertSame('Triggered error page', $exception->getMessage());
		$this->assertSame(404, $exception->getHttpCode());
	}
}
