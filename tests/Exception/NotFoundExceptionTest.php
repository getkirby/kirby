<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

class NotFoundExceptionTest extends TestCase
{
	#[CoversNothing]
	public function testDefaults(): void
	{
		$exception = new NotFoundException();
		$this->assertSame('error.notFound', $exception->getKey());
		$this->assertSame('Not found', $exception->getMessage());
		$this->assertSame(404, $exception->getHttpCode());
	}
}
