<?php

namespace Kirby\Cms;

use Closure;

class HelpersTestCase extends TestCase
{
	protected $hasErrorHandler = false;

	public function tearDown(): void
	{
		parent::tearDown();

		if ($this->hasErrorHandler === true) {
			restore_error_handler();
			$this->hasErrorHandler = false;
		}
	}

	public function assertError(
		int $expectedErrorType,
		string $exptectedErrorMessage,
		Closure $callback,
		bool $expectedFailure = true
	) {
		$this->hasErrorHandler = true;

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
