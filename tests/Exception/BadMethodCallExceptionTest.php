<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

class BadMethodCallExceptionTest extends TestCase
{
	#[CoversNothing]
	public function testDefaults(): void
	{
		$exception = new BadMethodCallException();
		$this->assertSame('error.invalidMethod', $exception->getKey());
		$this->assertSame('The method "-" does not exist', $exception->getMessage());
		$this->assertSame(400, $exception->getHttpCode());
		$this->assertSame(['method' => null], $exception->getData());
	}

	#[CoversNothing]
	public function testPlaceholders(): void
	{
		$exception = new BadMethodCallException(data: [
			'method' => 'get'
		]);
		$this->assertSame('The method "get" does not exist', $exception->getMessage());
		$this->assertSame(['method' => 'get'], $exception->getData());
	}

	#[CoversNothing]
	public function testPlaceholdersWithNamedArguments(): void
	{
		$exception = new BadMethodCallException(
			data: ['method' => 'get']
		);
		$this->assertSame('The method "get" does not exist', $exception->getMessage());
		$this->assertSame(['method' => 'get'], $exception->getData());
	}
}
