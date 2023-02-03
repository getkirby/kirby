<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \Kirby\Toolkit\Fluent */
class FluentTest extends TestCase
{
	/** @covers ::string */
	public function testFluentString()
	{
		$this->assertInstanceOf(FluentString::class, Fluent::string(''));
	}

	/** @covers ::array */
	public function testFluentArray()
	{
		$this->assertInstanceOf(FluentArray::class, Fluent::array([]));
	}

	/** @covers ::__call */
	public function testFluentCall()
	{
		$one = Fluent::array(['a' => 'b']);
		$two = $one->append(['c' => 'd']);

		$this->assertSame(['a' => 'b'], $one->value());
		$this->assertSame(['a' => 'b', 'c' => 'd'], $two->value());
	}

	/** @covers ::__call */
	public function testFluentEverything()	{
		$result = Fluent::string('a, b, c')
			->split(',')
			->filter(fn($value) => $value !== 'b')
			->append(['d', 'c'])
			->tap(fn($arr) => $this->assertSame(4, count($arr->value())))
			->join('')
			->replace('ac', 'ac/');

		$this->assertSame('ac/dc', $result->value());
	}
}
