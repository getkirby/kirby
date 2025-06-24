<?php

namespace Kirby\Query;

use Closure;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Deprecate in v6
 */
#[CoversClass(Argument::class)]
class ArgumentTest extends TestCase
{
	public function testFactory(): void
	{
		// strings
		$argument = Argument::factory(" ' 23 '  ");
		$this->assertSame(' 23 ', $argument->value);

		$argument = Argument::factory("'2\"3'");
		$this->assertSame('2"3', $argument->value);

		$argument = Argument::factory("'2\\\"3'");
		$this->assertSame('2\\"3', $argument->value);

		$argument = Argument::factory("'2\\'3'");
		$this->assertSame("2'3", $argument->value);

		$argument = Argument::factory('"2\'3"');
		$this->assertSame("2'3", $argument->value);

		$argument = Argument::factory('"2\\\'3"');
		$this->assertSame("2\\'3", $argument->value);

		$argument = Argument::factory('"2\\"3"');
		$this->assertSame('2"3', $argument->value);

		// arrays
		$argument = Argument::factory('[1, "a", 3]');
		$this->assertCount(3, $argument->value);

		// numbers
		$argument = Argument::factory(' 23  ');
		$this->assertSame(23, $argument->value);
		$argument = Argument::factory(' 23.3  ');
		$this->assertSame(23.3, $argument->value);

		// null
		$argument = Argument::factory(' null ');
		$this->assertNull($argument->value);

		// booleans
		$argument = Argument::factory(' true ');
		$this->assertTrue($argument->value);

		$argument = Argument::factory(' false ');
		$this->assertFalse($argument->value);

		$argument = Argument::factory(' ( "foo") ');
		$this->assertSame('foo', $argument->value);
	}

	public function testResolve(): void
	{
		// strings
		$argument = Argument::factory(" ' 23 '  ")->resolve();
		$this->assertSame(' 23 ', $argument);

		// arrays
		$argument = Argument::factory('[1, "a", 3.3]')->resolve();
		$this->assertSame([1, 'a', 3.3], $argument);

		// nested query
		$argument = Argument::factory('foo')->resolve(['foo' => 'bar']);
		$this->assertSame('bar', $argument);
	}

	public function testWithClosure(): void
	{
		$argument = Argument::factory('() => site.children');
		$this->assertInstanceOf(Closure::class, $argument->value);
		$this->assertInstanceOf(Closure::class, $argument->resolve());
	}
}
