<?php

namespace Kirby\Toolkit;

class ObjTest extends TestCase
{
	public function test__call()
	{
		$obj = new Obj([
			'foo' => 'bar'
		]);

		$this->assertEquals('bar', $obj->foo());
	}

	public function test__get()
	{
		$obj = new Obj();
		$this->assertNull($obj->foo);
	}

	public function testGetMultiple()
	{
		$obj = new Obj([
			'one' => 'first',
			'two' => 'second',
			'three' => 'third'
		]);

		$this->assertEquals('first', $obj->get('one'));
		$this->assertEquals(['one' => 'first', 'three' => 'third'], $obj->get(['one', 'three']));
		$this->assertEquals([
			'one' => 'first',
			'three' => 'third',
			'four' => 'fallback',
			'eight' => null
		], $obj->get(['one', 'three', 'four', 'eight'], ['four' => 'fallback']));
		$this->assertEquals($obj->toArray(), $obj->get(['one', 'two', 'three']));
	}

	public function testGetMultipleInvalidFallback()
	{
		$this->expectException('Kirby\Exception\InvalidArgumentException');
		$this->expectExceptionMessage('fallback value must be an array');

		$obj = new Obj(['one' => 'first']);
		$obj->get(['two'], 'invalid fallback');
	}

	public function testToArray()
	{
		$obj = new Obj($expected = [
			'foo' => 'bar'
		]);

		$this->assertEquals($expected, $obj->toArray());
	}

	public function testToArrayWithChild()
	{
		$parent = new Obj([
			'child' => new Obj([
				'foo' => 'bar'
			])
		]);

		$expected = [
			'child' => [
				'foo' => 'bar'
			]
		];

		$this->assertEquals($expected, $parent->toArray());
	}

	public function testToJson()
	{
		$obj = new Obj($expected = [
			'foo' => 'bar'
		]);

		$this->assertEquals(json_encode($expected), $obj->toJson());
	}

	public function test__debuginfo()
	{
		$obj = new Obj($expected = [
			'foo' => 'bar'
		]);

		$this->assertEquals($expected, $obj->__debugInfo());
	}
}
