<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserNotFoundException::class)]
class UserNotFoundExceptionTest extends TestCase
{
	public function testDefaults(): void
	{
		$exception = new UserNotFoundException('marge@simpsons.com');

		$this->assertSame('error.user.notFound', $exception->getKey());
		$this->assertSame('The user "marge@simpsons.com" cannot be found', $exception->getMessage());
		$this->assertSame(['name' => 'marge@simpsons.com'], $exception->getData());
		$this->assertSame(404, $exception->getHttpCode());
	}
}
