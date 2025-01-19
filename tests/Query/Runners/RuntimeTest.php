<?php

namespace Kirby\Query\Runners;

use Exception;

/**
 * @coversDefaultClass \Kirby\Query\Runners\Runtime
 */
class RuntimeTest extends TestCase
{
	/**
	 * @covers ::access
	 */
	public function testAccessWithArray(): void
	{
		$array  = [
			'bar' => 'bar',
			'bax' => fn (string $string = 'bax') => $string
		];

		$result = Runtime::access($array, 'bar');
		$this->assertSame('bar', $result);

		$result = Runtime::access($array, 'bax');
		$this->assertSame('bax', $result);

		$result = Runtime::access($array, 'bax', false, 'custom');
		$this->assertSame('custom', $result);

		$result = Runtime::access($array, 'fox');
		$this->assertNull($result);
	}

	/**
	 * @covers ::access
	 */
	public function testAccessWithObject(): void
	{
		$obj = new class {
			public string $bax = 'qox';

			public function print(string $string = 'bar'): string
			{
				return $string;
			}
		};

		$result = Runtime::access($obj, 'print');
		$this->assertSame('bar', $result);

		$result = Runtime::access($obj, 'print', false, 'custom');
		$this->assertSame('custom', $result);

		$result = Runtime::access($obj, 'bax');
		$this->assertSame('qox', $result);

		$result = Runtime::access($obj, 'fox');
		$this->assertNull($result);
	}

	/**
	 * @covers ::access
	 */
	public function testAccessWithNullSafe(): void
	{
		$result = Runtime::access(null, 'bar', true);
		$this->assertNull($result);
	}

	/**
	 * @covers ::access
	 */
	public function testAccessWithoutNullSafe(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cannot access "bar" on NULL');
		Runtime::access(null, 'bar', false);
	}

	/**
	 * @covers ::get
	 */
	public function testGet(): void
	{
		$context   = ['foo' => 'bar', 'qox' => fn () => 'bax'];
		$functions = ['fox' => fn () => 'fax'];

		$result = Runtime::get('foo', $context);
		$this->assertSame('bar', $result);

		$result = Runtime::get('qox', $context);
		$this->assertSame('bax', $result);

		$result = Runtime::get('fox', $context);
		$this->assertNull($result);

		$result = Runtime::get('fox', $context, $functions);
		$this->assertSame('fax', $result);
	}
}
