<?php

namespace Kirby\Toolkit;

use Closure;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\LazyValue
 */
class LazyValueTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::resolve
	 */
	public function testValue()
	{
		$expected = 'test';
		$value    = new LazyValue(fn () => $expected);
		$this->assertInstanceOf(LazyValue::class, $value);
		$this->assertNotInstanceOf(Closure::class, $value);
		$this->assertFalse(is_callable($value));
		$this->assertSame($expected, $value->resolve());
	}

	/**
	 * @covers ::unwrap
	 */
	public function testUnwrap()
	{
		$value = LazyValue::unwrap($expected = 'a');
		$this->assertSame($expected, $value);

		$expected = ['a', 'b', 'c'];
		$value    = LazyValue::unwrap($expected);
		$this->assertSame($expected, $value);

		$lazy  = new LazyValue(fn () => 'a');
		$value = LazyValue::unwrap($lazy);
		$this->assertSame('a', $value);

		$value = LazyValue::unwrap([$lazy, 'b', 'c']);
		$this->assertSame($expected, $value);
	}
}
