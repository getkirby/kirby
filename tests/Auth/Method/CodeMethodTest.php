<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Method::class)]
#[CoversClass(CodeMethod::class)]
class CodeMethodTest extends TestCase
{
	public function testAuthenticate(): void
	{
		$args   = null;
		$status = $this->createStub(Status::class);
		$auth   = $this->createStub(Auth::class);
		$auth->method('createChallenge')
			->willReturnCallback(function (...$x) use (&$args, $status) {
				$args = $x;
				return $status;
			});

		$method = new CodeMethod(auth: $auth);

		$result = $method->authenticate('marge@simpsons.com', long: true);
		$this->assertInstanceOf(Status::class, $result);
		$this->assertSame($status, $result);
		$this->assertSame(['marge@simpsons.com', true, 'login'], $args);

		$result = $method->authenticate('lisa@simpsons.com', long: false);
		$this->assertSame($status, $result);
		$this->assertSame(['lisa@simpsons.com', false, 'login'], $args);
	}

	public function testType(): void
	{
		$this->assertSame('code', CodeMethod::type());
	}
}
