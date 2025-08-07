<?php

namespace Kirby\Toolkit;

use Closure;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LazyValue::class)]
class LazyValueTest extends TestCase
{
	public function testValue(): void
	{
		$expected = 'test';
		$value    = new LazyValue(fn () => $expected);
		$this->assertInstanceOf(LazyValue::class, $value);
		$this->assertNotInstanceOf(Closure::class, $value);
		$this->assertIsNotCallable($value);
		$this->assertSame($expected, $value->resolve());
	}

	public function testUnwrap(): void
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
