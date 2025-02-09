<?php

namespace Kirby\Content;

use Kirby\Cms\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LockedContentException::class)]
class LockedContentExceptionTest extends TestCase
{
	public function testException()
	{
		$lock = new Lock(
			user: new User(['username' => 'test']),
			modified: $time = time()
		);

		$exception = new LockedContentException(
			lock: $lock
		);

		$this->assertSame('The version is locked', $exception->getMessage());
		$this->assertSame($lock->toArray(), $exception->getDetails());
		$this->assertSame(423, $exception->getHttpCode());
		$this->assertSame('error.content.lock', $exception->getKey());
	}

	public function testCustomMessage()
	{
		$lock = new Lock(
			user: new User(['username' => 'test']),
			modified: $time = time()
		);

		$exception = new LockedContentException(
			lock: $lock,
			message: $message = 'The version is locked and cannot be deleted'
		);

		$this->assertSame($message, $exception->getMessage());
	}
}
