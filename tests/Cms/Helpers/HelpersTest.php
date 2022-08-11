<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Obj;

/**
 * @coversDefaultClass Kirby\Cms\Helpers
 */
class HelpersTest extends TestCase
{
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
