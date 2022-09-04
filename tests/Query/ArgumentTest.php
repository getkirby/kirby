<?php

namespace Kirby\Query;

use Closure;

/**
 * @coversDefaultClass Kirby\Query\Argument
 */
class ArgumentTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::factory
	 */
	public function testFactory()
	{
		// strings
		$argument = Argument::factory(" ' 23 '  ");
		$this->assertSame(' 23 ', $argument->value);

		$argument = Argument::factory('"2\'3"');
		$this->assertSame('2\'3', $argument->value);

		$argument = Argument::factory('"2\\\'3"');
		$this->assertSame('2\'3', $argument->value);

		// arrays
		$argument = Argument::factory('[1, "a", 3]');
		$this->assertSame(3, $argument->value->count());

		// numbers
		$argument = Argument::factory(' 23  ');
		$this->assertSame(23.0, $argument->value);

		// null
		$argument = Argument::factory(' null ');
		$this->assertNull($argument->value);

		// booleans
		$argument = Argument::factory(' true ');
		$this->assertTrue($argument->value);

		$argument = Argument::factory(' false ');
		$this->assertFalse($argument->value);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve()
	{
		// strings
		$argument = Argument::factory(" ' 23 '  ")->resolve();
		$this->assertSame(' 23 ', $argument);

		// arrays
		$argument = Argument::factory('[1, "a", 3]')->resolve();
		$this->assertSame([1.0, 'a', 3.0], $argument);

		// nested query
		$argument = Argument::factory('foo')->resolve(['foo' => 'bar']);
		$this->assertSame('bar', $argument);
	}

	/**
	 * @covers ::factory
	 * @covers ::resolve
	 */
	public function testWithClosure()
	{
		$argument = Argument::factory('() => site.children');
		$this->assertInstanceOf(Closure::class, $argument->value);
		$this->assertInstanceOf(Closure::class, $argument->resolve());
	}
}
