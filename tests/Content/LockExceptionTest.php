<?php

namespace Kirby\Content;

use Kirby\Cms\User;

/**
 * @coversDefaultClass \Kirby\Content\LockException
 */
class LockExceptionTest extends TestCase
{
	public function testException()
	{
		$lock = new Lock(
			user: new User(['username' => 'test']),
			modified: $time = time()
		);

		$exception = new LockException(
			lock: $lock
		);

		$this->assertSame('The version is locked', $exception->getMessage());
		$this->assertSame($lock->toArray(), $exception->getDetails());
		$this->assertSame(423, $exception->getHttpCode());
		$this->assertSame('error.lock', $exception->getKey());
	}

	public function testCustomMessage()
	{
		$lock = new Lock(
			user: new User(['username' => 'test']),
			modified: $time = time()
		);

		$exception = new LockException(
			lock: $lock,
			message: $message = 'The version is locked and cannot be deleted'
		);

		$this->assertSame($message, $exception->getMessage());
	}
}
