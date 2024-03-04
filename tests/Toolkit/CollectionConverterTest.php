<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass \Kirby\Toolkit\Collection
 */
class CollectionConverterTest extends TestCase
{
	/**
	 * @covers ::toArray
	 */
	public function testToArray()
	{
		$array = [
			'one'   => 'eins',
			'two'   => 'zwei'
		];
		$collection = new Collection($array);
		$this->assertSame($array, $collection->toArray());
	}

	/**
	 * @covers ::toArray
	 */
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

	/**
	 * @covers ::toJson
	 */
	public function testToJson()
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei'
		]);
		$this->assertSame('{"one":"eins","two":"zwei"}', $collection->toJson());
	}

	/**
	 * @covers ::toString
	 */
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
