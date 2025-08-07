<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Obj;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Helpers::class)]
class HelpersTest extends HelpersTestCase
{
	protected array $deprecations = [];
	protected string $locale;

	public function setUp(): void
	{
		parent::setUp();

		$this->deprecations = Helpers::$deprecations;
		$this->locale       = setlocale(LC_MESSAGES, 0);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		Helpers::$deprecations = $this->deprecations;
		setlocale(LC_MESSAGES, $this->locale);
	}

	public function testDeprecated(): void
	{
		$this->assertError(
			E_USER_DEPRECATED,
			'The xyz method is deprecated.',
			fn () => Helpers::deprecated('The xyz method is deprecated.')
		);
	}

	public function testDeprecatedKeyUndefined(): void
	{
		$this->assertError(
			E_USER_DEPRECATED,
			'The xyz method is deprecated.',
			fn () => Helpers::deprecated('The xyz method is deprecated.', 'my-key')
		);
	}

	public function testDeprecatedActivated(): void
	{
		$this->assertError(
			E_USER_DEPRECATED,
			'The xyz method is deprecated.',
			function () {
				Helpers::$deprecations = ['my-key' => true];
				Helpers::deprecated('The xyz method is deprecated.', 'my-key');
			}
		);
	}

	public function testDeprecatedKeyDeactivated(): void
	{
		$result = $this->assertError(
			E_USER_DEPRECATED,
			'The xyz method is deprecated.',
			function () {
				Helpers::$deprecations = ['my-key' => false];
				return Helpers::deprecated('The xyz method is deprecated.', 'my-key');
			},
			false
		);
		$this->assertFalse($result);
	}

	public function testDumpOnCli(): void
	{
		$this->app = $this->app->clone([
			'cli' => true
		]);

		$this->assertSame("test\n", Helpers::dump('test', false));

		$this->expectOutputString("test\ntest\n");
		Helpers::dump('test');
		Helpers::dump('test', true);
	}

	public function testDumpOnServer(): void
	{
		$this->app = $this->app->clone([
			'cli' => false
		]);

		$this->assertSame('<pre>test</pre>', Helpers::dump('test', false));

		$this->expectOutputString('<pre>test1</pre><pre>test2</pre>');
		Helpers::dump('test1');
		Helpers::dump('test2', true);
	}

	public function testHandleErrorsNoWarning(): void
	{
		$this->assertSame('return', Helpers::handleErrors(
			fn () => 'return',
			fn () => $this->fail('Condition handler should not be called because no warning was triggered')
		));
	}

	public function testHandleErrorsException(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Exception inside the action');

		Helpers::handleErrors(
			fn () => throw new Exception(
				message: 'Exception inside the action'
			),
			fn () => $this->fail(
				'Condition handler should not be called because no warning was triggered'
			)
		);
	}

	public function testHandleErrorsLocaleReset(): void
	{
		setlocale(LC_MESSAGES, 'de_DE.UTF-8');

		try {
			Helpers::handleErrors(
				function () {
					$this->assertSame('C', setlocale(LC_MESSAGES, 0));
					throw new Exception(message: 'Exception inside the action');
				},
				fn () => $this->fail('Condition handler should not be called because no warning was triggered')
			);
		} catch (Exception $e) {
			$this->assertSame('Exception inside the action', $e->getMessage());
			$this->assertSame('de_DE.UTF-8', setlocale(LC_MESSAGES, 0));
		}
	}

	public function testHandleErrorsWarningCaught1(): void
	{
		$this->activeErrorHandlers++;

		$called = false;
		set_error_handler(function (int $errno, string $errstr) use (&$called) {
			$called = true;
		});

		$this->assertSame('handled', Helpers::handleErrors(
			fn () => trigger_error('Some warning', E_USER_WARNING),
			function (int $errno, string $errstr) {
				$this->assertSame(E_USER_WARNING, $errno);
				$this->assertSame('Some warning', $errstr);
				// drop error
				return true;
			},
			'handled'
		));

		$this->assertFalse($called);
	}

	public function testHandleErrorsWarningCaught2(): void
	{
		$this->activeErrorHandlers++;

		$called = false;
		set_error_handler(function (int $errno, string $errstr) use (&$called) {
			$called = true;

			$this->assertSame(E_USER_WARNING, $errno);
			$this->assertSame('Some warning', $errstr);
		});

		$this->assertTrue(Helpers::handleErrors(
			fn () => trigger_error('Some warning', E_USER_WARNING),
			function (int $errno, string $errstr) {
				$this->assertSame(E_USER_WARNING, $errno);
				$this->assertSame('Some warning', $errstr);

				// continue the handler chain
				return false;
			},
			'handled'
		));

		$this->assertTrue($called);
	}

	public function testHandleErrorsWarningCaughtCallbackValue(): void
	{
		$this->assertSame('handled', Helpers::handleErrors(
			fn () => trigger_error('Some warning', E_USER_WARNING),
			fn (int $errno, string $errstr) => true,
			fn () => 'handled'
		));
	}

	public function testHandleErrorsWarningNotCaught(): void
	{
		$this->assertError(
			E_USER_WARNING,
			'Some warning',
			function () {
				Helpers::handleErrors(
					fn () => trigger_error('Some warning', E_USER_WARNING),
					function (int $errno, string $errstr) {
						$this->assertSame(E_USER_WARNING, $errno);
						$this->assertSame('Some warning', $errstr);

						// continue the handler chain
						return false;
					},
					'handled'
				);
			}
		);
	}

	public function testSize(): void
	{
		// number
		$this->assertSame(3, Helpers::size(3));

		// string
		$this->assertSame(3, Helpers::size('abc'));

		// array
		$this->assertSame(3, Helpers::size(['a', 'b', 'c']));

		// collection
		$this->assertSame(3, Helpers::size(new Collection(['a', 'b', 'c'])));

		// invalid type
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Could not determine the size of the given value');
		Helpers::size(new Obj());
	}
}
