<?php

namespace Kirby\Exception;

use Kirby\TestCase;

class DuplicateExceptionTest extends TestCase
{
	/**
	 * @coversNothing
	 */
	public function testDefaults()
	{
		$exception = new DuplicateException();
		$this->assertSame('error.duplicate', $exception->getKey());
		$this->assertSame('The entry exists', $exception->getMessage());
		$this->assertSame(400, $exception->getHttpCode());
	}
}
