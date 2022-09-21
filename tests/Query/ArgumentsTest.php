<?php

namespace Kirby\Query;

/**
 * @coversDefaultClass Kirby\Query\Arguments
 */
class ArgumentsTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$arguments = Arguments::factory('1, 2, 3');
		$this->assertSame(3, $arguments->count());

		$arguments = Arguments::factory('1, 2, [3, 4]');
		$this->assertSame(3, $arguments->count());

		$arguments = Arguments::factory('1, 2, \'3, 4\'');
		$this->assertSame(3, $arguments->count());

		$arguments = Arguments::factory('1, 2, "3, 4"');
		$this->assertSame(3, $arguments->count());

		$arguments = Arguments::factory('1, 2, (3, 4)');
		$this->assertSame(3, $arguments->count());
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve()
	{
		$arguments = Arguments::factory('1, 2, 3');
		$this->assertSame([1.0 , 2.0, 3.0], $arguments->resolve());

		$arguments = Arguments::factory('1, 2, [3, 4]');
		$this->assertSame([1.0 , 2.0, [3.0, 4.0]], $arguments->resolve());

		$arguments = Arguments::factory('1, 2, \'3, 4\'');
		$this->assertSame([1.0 , 2.0, '3, 4'], $arguments->resolve());

		$arguments = Arguments::factory('1, 2, "3, 4"');
		$this->assertSame([1.0 , 2.0, '3, 4'], $arguments->resolve());

		$arguments = Arguments::factory('1, 2, \'(3, 4)\'');
		$this->assertSame([1.0 , 2.0, '(3, 4)'], $arguments->resolve());
	}
}
