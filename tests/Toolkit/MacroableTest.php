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
		Str::$methods['test'] = fn () => 'test';
		Str::$methods['wrapInStrong'] = fn ($string) => "<strong>{$string}</strong>";
		A::$methods['test'] = fn (array $array) => A::map($array, fn ($value) => $value . 'test');
		A::$methods['test2'] = [A::class, 'map'];
	}

	/**
	 * @covers ::__callStatic
	 */
	public function testCallStatic()
	{
		$this->assertEquals('test', Str::test());
		$this->assertEquals('<strong>test</strong>', Str::wrapInStrong('test'));

		$this->assertEquals(['1test', '2test'], A::test([1, 2]));
		$this->assertEquals(['1test', '2test'], A::test2([1, 2], fn ($value) => $value . 'test'));
	}

	/**
	 * @covers ::__callStatic
	 */
	public function testCallStaticNonExistent()
	{
		$this->expectException('Exception');
		$this->expectExceptionMessage('Class "Kirby\Toolkit\Str" does not contain method "doesntExist"');

		Str::doesntExist();
	}
}
