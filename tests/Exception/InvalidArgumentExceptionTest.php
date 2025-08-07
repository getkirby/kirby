<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

class InvalidArgumentExceptionTest extends TestCase
{
	#[CoversNothing]
	public function testDefaults(): void
	{
		$exception = new InvalidArgumentException();
		$this->assertSame('error.invalidArgument', $exception->getKey());
		$this->assertSame('Invalid argument "-" in method "-"', $exception->getMessage());
		$this->assertSame(400, $exception->getHttpCode());
		$this->assertSame(['argument' => null, 'method' => null], $exception->getData());
	}

	#[CoversNothing]
	public function testPlaceholders(): void
	{
		$exception = new InvalidArgumentException(data: [
			'argument' => 'key',
			'method' => 'get'
		]);
		$this->assertSame('Invalid argument "key" in method "get"', $exception->getMessage());
		$this->assertSame([
			'argument' => 'key',
			'method' => 'get'
		], $exception->getData());
	}

	#[CoversNothing]
	public function testPlaceholdersWithNamedArguments(): void
	{
		$exception = new InvalidArgumentException(
			data: [
				'argument' => 'key',
				'method' => 'get'
			]
		);
		$this->assertSame('Invalid argument "key" in method "get"', $exception->getMessage());
		$this->assertSame([
			'argument' => 'key',
			'method' => 'get'
		], $exception->getData());
	}
}
