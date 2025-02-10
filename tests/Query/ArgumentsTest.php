<?php

namespace Kirby\Query;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Arguments::class)]
class ArgumentsTest extends TestCase
{
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
