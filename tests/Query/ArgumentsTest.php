<?php

namespace Kirby\Query;

/**
 * @coversDefaultClass Kirby\Query\Arguments
 */
class ArgumentsTest extends \Kirby\TestCase
{
	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$arguments = Arguments::factory('1, 2, 3');
		$this->assertCount(3, $arguments);

		$arguments = Arguments::factory('1, 2, [3, 4]');
		$this->assertCount(3, $arguments);

		$arguments = Arguments::factory('1, 2, \'3, 4\'');
		$this->assertCount(3, $arguments);

		$arguments = Arguments::factory('1, 2, "3, 4"');
		$this->assertCount(3, $arguments);

		$arguments = Arguments::factory('1, 2, (3, 4)');
		$this->assertCount(3, $arguments);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve()
	{
		$arguments = Arguments::factory('1, 2.3, 3');
		$this->assertSame([1, 2.3, 3], $arguments->resolve());

		$arguments = Arguments::factory('1, 2, [3.3, 4]');
		$this->assertSame([1, 2, [3.3, 4]], $arguments->resolve());

		$arguments = Arguments::factory('1, 2, \'3, 4\'');
		$this->assertSame([1, 2, '3, 4'], $arguments->resolve());

		$arguments = Arguments::factory('1, 2, "3, 4"');
		$this->assertSame([1, 2, '3, 4'], $arguments->resolve());

		$arguments = Arguments::factory('1, 2, \'(3, 4)\'');
		$this->assertSame([1, 2, '(3, 4)'], $arguments->resolve());
	}
}
