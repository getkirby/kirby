<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Obj;

/**
 * @coversDefaultClass \Kirby\Cms\Helpers
 */
class HelpersTest extends HelpersTestCase
{
	protected array $deprecations = [];

	public function setUp(): void
	{
		parent::setUp();

		$this->deprecations = Helpers::$deprecations;
	}

	public function tearDown(): void
	{
		parent::tearDown();

		Helpers::$deprecations = $this->deprecations;
	}

	/**
	 * @covers ::deprecated
	 */
	public function testDeprecated()
	{
		$this->assertError(
			E_USER_DEPRECATED,
			'The xyz method is deprecated.',
			fn () => Helpers::deprecated('The xyz method is deprecated.')
		);
	}

	/**
	 * @covers ::deprecated
	 */
	public function testDeprecatedKeyUndefined()
	{
		$this->assertError(
			E_USER_DEPRECATED,
			'The xyz method is deprecated.',
			fn () => Helpers::deprecated('The xyz method is deprecated.', 'my-key')
		);
	}

	/**
	 * @covers ::deprecated
	 */
	public function testDeprecatedActivated()
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

	/**
	 * @covers ::deprecated
	 */
	public function testDeprecatedKeyDeactivated()
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

	/**
	 * @covers ::dump
	 */
	public function testDumpOnCli()
	{
		$this->app = $this->app->clone([
			'cli' => true
		]);

		$this->assertSame("test\n", Helpers::dump('test', false));

		$this->expectOutputString("test\ntest\n");
		Helpers::dump('test');
		Helpers::dump('test', true);
	}

	/**
	 * @covers ::dump
	 */
	public function testDumpOnServer()
	{
		$this->app = $this->app->clone([
			'cli' => false
		]);

		$this->assertSame('<pre>test</pre>', Helpers::dump('test', false));

		$this->expectOutputString('<pre>test1</pre><pre>test2</pre>');
		Helpers::dump('test1');
		Helpers::dump('test2', true);
	}

	/**
	 * @covers ::handleErrors
	 */
	public function testHandleErrorsNoWarning()
	{
		$this->assertSame('return', Helpers::handleErrors(
			fn () => 'return',
			fn () => $this->fail('Condition handler should not be called because no warning was triggered')
		));
	}

	/**
	 * @covers ::handleErrors
	 */
	public function testHandleErrorsException()
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

	/**
	 * @covers ::handleErrors
	 */
	public function testHandleErrorsWarningCaught1()
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

	/**
	 * @covers ::handleErrors
	 */
	public function testHandleErrorsWarningCaught2()
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

	/**
	 * @covers ::handleErrors
	 */
	public function testHandleErrorsWarningCaughtCallbackValue()
	{
		$this->assertSame('handled', Helpers::handleErrors(
			fn () => trigger_error('Some warning', E_USER_WARNING),
			fn (int $errno, string $errstr) => true,
			fn () => 'handled'
		));
	}

	/**
	 * @covers ::handleErrors
	 */
	public function testHandleErrorsWarningNotCaught()
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

	/**
	 * @covers ::size
	 */
	public function testSize()
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
