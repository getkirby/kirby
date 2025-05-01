<?php

namespace Kirby\Toolkit;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Obj::class)]
class ObjTest extends TestCase
{
	/**
	 * covers ::__construct
	 */
	public function test__call()
	{
		$obj = new Obj(['foo' => 'bar']);
		$this->assertSame('bar', $obj->foo());
	}

	public function test__get()
	{
		$obj = new Obj();
		$this->assertNull($obj->foo);
	}

	public function testGetMultiple()
	{
		$obj = new Obj([
			'one'   => 'first',
			'two'   => 'second',
			'three' => 'third'
		]);

		$this->assertSame('first', $obj->get('one'));
		$this->assertSame(['one' => 'first', 'three' => 'third'], $obj->get(['one', 'three']));
		$this->assertSame([
			'one'   => 'first',
			'three' => 'third',
			'four'  => 'fallback',
			'eight' => null
		], $obj->get(['one', 'three', 'four', 'eight'], ['four' => 'fallback']));
		$this->assertSame($obj->toArray(), $obj->get(['one', 'two', 'three']));
	}

	public function testGetMultipleInvalidFallback()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('fallback value must be an array');

		$obj = new Obj(['one' => 'first']);
		$obj->get(['two'], 'invalid fallback');
	}

	public function testToArray()
	{
		$obj = new Obj($expected = ['foo' => 'bar']);
		$this->assertSame($expected, $obj->toArray());
	}

	public function testToArrayWithChild()
	{
		$parent = new Obj([
			'child' => new Obj(['foo' => 'bar'])
		]);

		$expected = [
			'child' => [
				'foo' => 'bar'
			]
		];

		$this->assertSame($expected, $parent->toArray());
	}

	public function testToJson()
	{
		$obj = new Obj($expected = ['foo' => 'bar']);
		$this->assertSame(json_encode($expected), $obj->toJson());
	}

	public function testToKeys()
	{
		$obj = new Obj(['foo' => 'bar']);
		$this->assertSame(['foo'], $obj->toKeys());

		$obj = new Obj(['foo' => 'bar', 'one' => 'first']);
		$this->assertSame(['foo', 'one'], $obj->toKeys());
	}

	public function test__debugInfo()
	{
		$obj = new Obj($expected = ['foo' => 'bar']);
		$this->assertSame($expected, $obj->__debugInfo());
	}
}
