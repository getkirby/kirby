<?php

namespace Kirby\Query\Runners;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Scope::class)]
class ScopeTest extends TestCase
{
	public function testAccessWithArray(): void
	{
		$array  = [
			'bar' => 'bar',
			'bax' => fn (string $string = 'bax') => $string
		];

		$result = Scope::access($array, 'bar');
		$this->assertSame('bar', $result);

		$result = Scope::access($array, 'bax');
		$this->assertSame('bax', $result);

		$result = Scope::access($array, 'bax', false, 'custom');
		$this->assertSame('custom', $result);

		$result = Scope::access($array, 'fox');
		$this->assertNull($result);
	}

	public function testAccessWithArrayValueAndArguments(): void
	{
		$array = [
			'zero'     => 0,
			'empty'    => '',
			'false'    => false,
			'list'     => [],
			'function' => 'strtoupper',
			'callable' => [new Exception('message'), 'getMessage'],
		];

		// only closures get called, any other value
		// is returned as-is, ignoring the arguments
		$this->assertSame(0, Scope::access($array, 'zero', false, 'a'));
		$this->assertSame('', Scope::access($array, 'empty', false, 'a'));
		$this->assertFalse(Scope::access($array, 'false', false, 'a'));
		$this->assertSame([], Scope::access($array, 'list', false, 'a'));
		$this->assertSame('strtoupper', Scope::access($array, 'function', false, 'a'));
		$this->assertSame($array['callable'], Scope::access($array, 'callable', false, 'a'));
	}

	public function testAccessWithObject(): void
	{
		$obj = new class () {
			public string $bax = 'qox';

			public function print(string $string = 'bar'): string
			{
				return $string;
			}
		};

		$result = Scope::access($obj, 'print');
		$this->assertSame('bar', $result);

		$result = Scope::access($obj, 'print', false, 'custom');
		$this->assertSame('custom', $result);

		$result = Scope::access($obj, 'bax');
		$this->assertSame('qox', $result);

		$result = Scope::access($obj, 'fox');
		$this->assertNull($result);
	}

	public function testAccessWithNullSafe(): void
	{
		$result = Scope::access(null, 'bar', true);
		$this->assertNull($result);
	}

	public function testAccessWithoutNullSafe(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cannot access "bar" on NULL');
		Scope::access(null, 'bar', false);
	}

	public function testGet(): void
	{
		$context   = ['foo' => 'bar', 'qox' => fn () => 'bax'];
		$functions = ['fox' => fn () => 'fax'];

		$result = Scope::get('foo', $context);
		$this->assertSame('bar', $result);

		$result = Scope::get('qox', $context);
		$this->assertSame('bax', $result);

		$result = Scope::get('fox', $context);
		$this->assertNull($result);

		$result = Scope::get('fox', $context, $functions);
		$this->assertSame('fax', $result);
	}

	public function testGetWithNullInContext(): void
	{
		$functions = ['fox' => fn () => 'fax'];

		// a context entry wins over a global function,
		// even if its value is null
		$result = Scope::get('fox', ['fox' => null], $functions);
		$this->assertNull($result);

		$result = Scope::get('fox', ['fox' => null], [], false);
		$this->assertNull($result);
	}
}
