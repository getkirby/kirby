<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Obj;

/**
 * @coversDefaultClass Kirby\Cms\Helpers
 */
class HelpersTest extends TestCase
{
	protected $hasErrorHandler = false;

	public function tearDown(): void
	{
		if ($this->hasErrorHandler === true) {
			restore_error_handler();
			$this->hasErrorHandler = false;
		}
	}

	/**
	 * @covers ::deprecated
	 */
	public function testDeprecated()
	{
		// with disabled debug mode
		$this->assertFalse(Helpers::deprecated('The xyz method is deprecated.'));

		$this->app = $this->app->clone([
			'options' => [
				'debug' => true
			]
		]);

		// with enabled debug mode
		$this->expectException('Whoops\Exception\ErrorException');
		$this->expectExceptionMessage('The xyz method is deprecated.');
		Helpers::deprecated('The xyz method is deprecated.');
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
			function (&$override) {
				$this->fail('Handler should not be called because no warning was triggered');
			}
		));
	}

	/**
	 * @covers ::handleErrors
	 */
	public function testHandleErrorsWarningCaught1()
	{
		$this->hasErrorHandler = true;

		$called = false;
		set_error_handler(function (int $errno, string $errstr) use (&$called) {
			$called = true;
		});

		$this->assertSame('handled', Helpers::handleErrors(
			fn () => trigger_error('Some warning', E_USER_WARNING),
			function (&$override, int $errno, string $errstr) {
				$this->assertSame(E_USER_WARNING, $errno);
				$this->assertSame('Some warning', $errstr);

				$override = 'handled';

				// drop error
				return true;
			}
		));

		$this->assertFalse($called);
	}

	/**
	 * @covers ::handleErrors
	 */
	public function testHandleErrorsWarningCaught2()
	{
		$this->hasErrorHandler = true;

		$called = false;
		set_error_handler(function (int $errno, string $errstr) use (&$called) {
			$called = true;

			$this->assertSame(E_USER_WARNING, $errno);
			$this->assertSame('Some warning', $errstr);
		});

		$this->assertSame('handled', Helpers::handleErrors(
			fn () => trigger_error('Some warning', E_USER_WARNING),
			function (&$override, int $errno, string $errstr) {
				$this->assertSame(E_USER_WARNING, $errno);
				$this->assertSame('Some warning', $errstr);

				$override = 'handled';

				// continue the handler chain
				return false;
			}
		));

		$this->assertTrue($called);
	}

	/**
	 * @covers ::handleErrors
	 */
	public function testHandleErrorsWarningNotCaught()
	{
		$this->expectExceptionMessage('Some warning');

		Helpers::handleErrors(
			fn () => trigger_error('Some warning', E_USER_WARNING),
			function (&$override, int $errno, string $errstr) {
				$this->assertSame(E_USER_WARNING, $errno);
				$this->assertSame('Some warning', $errstr);

				$override = 'handled';

				// continue the handler chain
				return false;
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
		$this->expectException('Kirby\Exception\InvalidArgumentException');
		$this->expectExceptionMessage('Could not determine the size of the given value');
		Helpers::size(new Obj());
	}
}
