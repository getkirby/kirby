<?php

namespace Kirby\Cms;

use Closure;

class HelpersTestCase extends TestCase
{
	protected int $activeErrorHandlers = 0;

	public function tearDown(): void
	{
		parent::tearDown();

		while ($this->activeErrorHandlers > 0) {
			restore_error_handler();
			$this->activeErrorHandlers--;
		}
	}

	public function assertError(
		int $expectedErrorType,
		string $exptectedErrorMessage,
		Closure $callback,
		bool $expectedFailure = true
	) {
		$this->activeErrorHandlers++;

		$called = false;

		set_error_handler(
			function (int $errno, string $errstr) use ($expectedErrorType, $exptectedErrorMessage, &$called) {
				$called = true;
				$this->assertSame($expectedErrorType, $errno);
				$this->assertSame($exptectedErrorMessage, $errstr);
			}
		);

		$result = $callback();

		if ($expectedFailure === false) {
			$this->assertFalse($called);
			return $result;
		}

		$this->assertTrue($called);
	}
}
