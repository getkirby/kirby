<?php

namespace Kirby\Toolkit;

class CollectionConverterTest extends TestCase
{
	public function testToArray()
	{
		$array = [
			'one'   => 'eins',
			'two'   => 'zwei'
		];
		$collection = new Collection($array);
		$this->assertSame($array, $collection->toArray());
	}

	public function testToArrayMap()
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei'
		]);
		$this->assertSame([
			'one'   => 'einsy',
			'two'   => 'zweiy'
		], $collection->toArray(function ($item) {
			return $item . 'y';
		}));
	}

	public function testToJson()
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei'
		]);
		$this->assertSame('{"one":"eins","two":"zwei"}', $collection->toJson());
	}

	public function testToString()
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei'
		]);
		$string = 'one<br />two';
		$this->assertSame($string, $collection->toString());
		$this->assertSame($string, (string)$collection);
	}
}
