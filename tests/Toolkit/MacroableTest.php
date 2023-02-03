<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\Macroable
 */
class MacroableTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		Str::_addMacro('test', function () {
			return 'test';
		});
		Str::_addMacro('wrapInStrong', function ($string) {
			return '<strong>' . $string . '</strong>';
		});
		A::_addMacro('test', function (array $array) {
			return A::map($array, function ($value) {
				return $value . 'test';
			});
		});
	}

	/**
	 * @covers ::_addMacro
	 */
	public function testAddMacroWhenItExists()
	{
		$this->expectException('Exception');
		$this->expectExceptionMessage('Class "Kirby\Toolkit\Str" already includes macro "test"');

		Str::_addMacro('test', function () {
			return 'test';
		});
	}

	/**
	 * @covers ::_addMacro
	 */
	public function testAddMacroNamedAfterExistingMethod()
	{
		$this->expectException('Exception');
		$this->expectExceptionMessage('Class "Kirby\Toolkit\Str" already contains static method "upper"');

		Str::_addMacro('upper', function () {
			return 'oh no';
		});
	}

	/**
	 * @covers ::_addMacro
	 * @covers ::_hasMacro
	 */
	public function testHasMacro()
	{
		$this->assertTrue(Str::_hasMacro('test'));
		$this->assertTrue(Str::_hasMacro('wrapInStrong'));
		$this->assertFalse(Str::_hasMacro('test2'));

		$this->assertTrue(A::_hasMacro('test'));
		$this->assertFalse(A::_hasMacro('test2'));
	}

	/**
	 * @covers ::__callStatic
	 */
	public function testCallStatic()
	{
		$this->assertEquals('test', Str::test());
		$this->assertEquals('<strong>test</strong>', Str::wrapInStrong('test'));

		$this->assertEquals(['1test', '2test'], A::test([1, 2]));
	}
}
