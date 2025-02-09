<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\Attributes\DataProvider;

class MockCollectionEntry
{
	public function __construct(
		protected string $value
	) {
	}

	public function __toString(): string
	{
		return $this->value;
	}
}

class CollectionFilterTest extends TestCase
{
	public function testFilterArray()
	{
		$collection = new Collection([
			[
				'name'  => 'Bastian',
				'role'  => 'developer',
				'color' => 'red'
			],
			[
				'name' => 'Nico',
				'role' => 'developer',
				'color' => 'green'
			],
			[
				'name'  => 'Sonja',
				'role'  => 'support',
				'color' => 'red'
			]
		]);

		$expected = [
			[
				'name'  => 'Bastian',
				'role'  => 'developer',
				'color' => 'red'
			]
		];

		$result = $collection->filter([
			['role', '==', 'developer'],
			['color', '==', 'red']
		]);
		$this->assertInstanceOf(Collection::class, $result);
		$this->assertSame($expected, $result->data());

		$result = $collection->filterBy([
			['role', '==', 'developer'],
			['color', '==', 'red']
		]);
		$this->assertInstanceOf(Collection::class, $result);
		$this->assertSame($expected, $result->data());
	}

	public function testFilterClosure()
	{
		$collection = new Collection([
			[
				'name'  => 'Bastian',
				'role'  => 'founder'
			],
			[
				'name' => 'Nico',
				'role' => 'developer'
			]
		]);

		$expected = [
			[
				'name'  => 'Bastian',
				'role'  => 'founder'
			]
		];

		$result = $collection->filter(fn ($item) => $item['role'] === 'founder');
		$this->assertInstanceOf(Collection::class, $result);
		$this->assertSame($expected, $result->data());

		$result = $collection->filterBy(fn ($item) => $item['role'] === 'founder');
		$this->assertInstanceOf(Collection::class, $result);
		$this->assertSame($expected, $result->data());
	}

	public static function filterDataProvider(): array
	{
		return [

			// EQUALS

			// strings
			[
				'attributes' => ['a' => 'a', 'b' => 'b', 'c' => 'a'],
				'operator'   =>  '==',
				'test'       => 'a',
				'expected'   => ['a', 'c'],
				'split'      => false
			],

			// split strings
			[
				'attributes' => ['a' => 'a, b', 'b' => 'b, c', 'c' => 'c, d'],
				'operator'   =>  '==',
				'test'       => 'b',
				'expected'   => ['a', 'b'],
				'split'      => ','
			],

			// booleans
			[
				'attributes' => ['a' => true, 'b' => true, 'c' => false],
				'operator'   =>  '==',
				'test'       => true,
				'expected'   => ['a', 'b'],
				'split'      => false
			],

			// objects with booleans
			[
				'attributes' => ['a' => new MockCollectionEntry('true'), 'b' => new MockCollectionEntry(true), 'c' => new MockCollectionEntry(false)],
				'operator'   =>  '==',
				'test'       => true,
				'expected'   => ['a', 'b'],
				'split'      => false
			],

			// ints
			[
				'attributes' => ['a' => '1', 'b' => 1, 'c' => 2],
				'operator'   =>  '==',
				'test'       => 1,
				'expected'   => ['a', 'b'],
				'split'      => false
			],

			// objects with ints
			[
				'attributes' => ['a' => new MockCollectionEntry('1'), 'b' => new MockCollectionEntry(1), 'c' => new MockCollectionEntry(2)],
				'operator'   =>  '==',
				'test'       => 1,
				'expected'   => ['a', 'b'],
				'split'      => false
			],

			// floats
			[
				'attributes' => ['a' => '1.1', 'b' => 1.1, 'c' => 2],
				'operator'   =>  '==',
				'test'       => 1.1,
				'expected'   => ['a', 'b'],
				'split'      => false
			],

			// objects with floats
			[
				'attributes' => ['a' => new MockCollectionEntry('1.1'), 'b' => new MockCollectionEntry(1.1), 'c' => new MockCollectionEntry(2)],
				'operator'   =>  '==',
				'test'       => 1.1,
				'expected'   => ['a', 'b'],
				'split'      => false
			],

			// NOT EQUALS

			// strings
			[
				'attributes' => ['a' => 'a', 'b' => 'b', 'c' => 'a'],
				'operator'   =>  '!=',
				'test'       => 'a',
				'expected'   => ['b'],
				'split'      => false
			],

			// split strings
			[
				'attributes' => ['a' => 'a, b', 'b' => 'b, c', 'c' => 'c, d'],
				'operator'   =>  '!=',
				'test'       => 'b',
				'expected'   => ['c'],
				'split'      => ','
			],

			// booleans
			[
				'attributes' => ['a' => true, 'b' => true, 'c' => false],
				'operator'   =>  '!=',
				'test'       => true,
				'expected'   => ['c'],
				'split'      => false
			],

			// objects with booleans
			[
				'attributes' => ['a' => new MockCollectionEntry('true'), 'b' => new MockCollectionEntry(true), 'c' => new MockCollectionEntry(false)],
				'operator'   =>  '!=',
				'test'       => true,
				'expected'   => ['c'],
				'split'      => false
			],

			// IN
			[
				'attributes' => ['a' => 'a', 'b' => 'b', 'c' => 'c'],
				'operator'   =>  'in',
				'test'       => ['a', 'c'],
				'expected'   => ['a', 'c'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'aa, ab', 'b' => 'ab, ac', 'c' => 'ad, ae'],
				'operator'   =>  'in',
				'test'       => ['aa', 'ab'],
				'expected'   => ['a', 'b'],
				'split'      => ','
			],

			// NOT IN
			[
				'attributes' => ['a' => 'a', 'b' => 'b', 'c' => 'c'],
				'operator'   =>  'not in',
				'test'       => ['a', 'c'],
				'expected'   => ['b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'aa, ab', 'b' => 'ab, ac', 'c' => 'ad, ae'],
				'operator'   =>  'not in',
				'test'       => ['aa', 'ab'],
				'expected'   => ['c'],
				'split'      => ','
			],

			// CONTAINS
			[
				'attributes' => ['a' => 'abc', 'b' => 'def'],
				'operator'   =>  '*=',
				'test'       => 'b',
				'expected'   => ['a'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'aa, ab', 'b' => 'ba, bb', 'c' => 'ca'],
				'operator'   =>  '*=',
				'test'       => 'b',
				'expected'   => ['a', 'b'],
				'split'      => ','
			],

			// NOT CONTAINS
			[
				'attributes' => ['a' => 'abc', 'b' => 'def'],
				'operator'   =>  '!*=',
				'test'       => 'b',
				'expected'   => ['b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'aa, ab', 'b' => 'ba, bb', 'c' => 'ca'],
				'operator'   =>  '!*=',
				'test'       => 'b',
				'expected'   => ['c'],
				'split'      => ','
			],

			// MORE
			[
				'attributes' => ['a' => 1, 'b' => 2],
				'operator'   =>  '>',
				'test'       => 1,
				'expected'   => ['b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
				'operator'   =>  '>',
				'test'       => 2,
				'expected'   => ['b', 'c'],
				'split'      => ','
			],

			// MIN
			[
				'attributes' => ['a' => 1, 'b' => 2, 'c' => 3],
				'operator'   =>  '>=',
				'test'       => 2,
				'expected'   => ['b', 'c'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
				'operator'   =>  '>=',
				'test'       => 3,
				'expected'   => ['b', 'c'],
				'split'      => ','
			],

			// LESS
			[
				'attributes' => ['a' => 1, 'b' => 2],
				'operator'   =>  '<',
				'test'       => 2,
				'expected'   => ['a'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
				'operator'   =>  '<',
				'test'       => 5,
				'expected'   => ['a', 'b'],
				'split'      => ','
			],

			// MAX
			[
				'attributes' => ['a' => 1, 'b' => 2, 'c' => 3],
				'operator'   =>  '<=',
				'test'       => 2,
				'expected'   => ['a', 'b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
				'operator'   =>  '<=',
				'test'       => 4,
				'expected'   => ['a', 'b'],
				'split'      => ','
			],

			// STARTS WITH
			[
				'attributes' => ['a' => 'aa', 'b' => 'bb'],
				'operator'   =>  '^=',
				'test'       => 'a',
				'expected'   => ['a'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'a foo, a bar', 'b' => 'b foo, c bar', 'c' => 'c foo, c bar'],
				'operator'   =>  '^=',
				'test'       => 'c',
				'expected'   => ['b', 'c'],
				'split'      => ','
			],

			// NOT STARTS WITH
			[
				'attributes' => ['a' => 'aa', 'b' => 'bb'],
				'operator'   =>  '!^=',
				'test'       => 'a',
				'expected'   => ['b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'a foo, a bar', 'b' => 'b foo, c bar', 'c' => 'c foo, c bar'],
				'operator'   =>  '!^=',
				'test'       => 'c',
				'expected'   => ['a'],
				'split'      => ','
			],

			// ENDS WITH
			[
				'attributes' => ['a' => 'aa', 'b' => 'bb'],
				'operator'   =>  '$=',
				'test'       => 'a',
				'expected'   => ['a'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'foo a, bar a', 'b' => 'foo b, bar c', 'c' => 'foo c, bar c'],
				'operator'   =>  '$=',
				'test'       => 'c',
				'expected'   => ['b', 'c'],
				'split'      => ','
			],

			// NOT ENDS WITH
			[
				'attributes' => ['a' => 'aa', 'b' => 'bb'],
				'operator'   =>  '!$=',
				'test'       => 'a',
				'expected'   => ['b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'foo a, bar a', 'b' => 'foo b, bar c', 'c' => 'foo c, bar c'],
				'operator'   =>  '!$=',
				'test'       => 'c',
				'expected'   => ['a'],
				'split'      => ','
			],

			// BETWEEN
			[
				'attributes' => ['a' => 1, 'b' => 2, 'c' => 3],
				'operator'   =>  'between',
				'test'       => [2, 3],
				'expected'   => ['b', 'c'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
				'operator'   =>  'between',
				'test'       => [1, 4],
				'expected'   => ['a', 'b'],
				'split'      => ','
			],
			[
				'attributes' => ['a' => 1, 'b' => 2, 'c' => 3],
				'operator'   =>  '..',
				'test'       => [2, 3],
				'expected'   => ['b', 'c'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '1, 2', 'b' => '3, 4', 'c' => '5, 6'],
				'operator'   =>  '..',
				'test'       => [1, 4],
				'expected'   => ['a', 'b'],
				'split'      => ','
			],

			// MATCH
			[
				'attributes' => ['a' => 'abc', 'b' => 'ABC'],
				'operator'   =>  '*',
				'test'       => '/[a-z]+/',
				'expected'   => ['a'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'abc, def', 'b' => 'ABC, DEF', 'c' => 'abc, DEF'],
				'operator'   =>  '*',
				'test'       => '/[a-z]+/',
				'expected'   => ['a', 'c'],
				'split'      => ','
			],

			// NOT MATCH
			[
				'attributes' => ['a' => 'abc', 'b' => 'ABC'],
				'operator'   =>  '!*',
				'test'       => '/[a-z]+/',
				'expected'   => ['b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'abc, def', 'b' => 'ABC, DEF', 'c' => 'abc, DEF'],
				'operator'   =>  '!*',
				'test'       => '/[a-z]+/',
				'expected'   => ['b'],
				'split'      => ','
			],

			// MINLENGTH
			[
				'attributes' => ['a' => 'abc', 'b' => 'defg'],
				'operator'   =>  'minlength',
				'test'       => 4,
				'expected'   => ['b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'abc', 'b' => 'defg', 'c' => 'hijklm'],
				'operator'   =>  'minlength',
				'test'       => 4,
				'expected'   => ['b', 'c'],
				'split'      => ','
			],

			// MAXLENGTH
			[
				'attributes' => ['a' => 'abc', 'b' => 'defg'],
				'operator'   =>  'maxlength',
				'test'       => 3,
				'expected'   => ['a'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'abc', 'b' => 'defg', 'c' => 'hijklm'],
				'operator'   =>  'maxlength',
				'test'       => 3,
				'expected'   => ['a'],
				'split'      => ','
			],

			// MINWORDS
			[
				'attributes' => ['a' => 'hello world', 'b' => 'hello'],
				'operator'   =>  'minwords',
				'test'       => 2,
				'expected'   => ['a'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'hello world, so great', 'b' => 'hello, great'],
				'operator'   =>  'minwords',
				'test'       => 2,
				'expected'   => ['a'],
				'split'      => ','
			],

			// MAXWORDS
			[
				'attributes' => ['a' => 'hello world', 'b' => 'hello'],
				'operator'   =>  'maxwords',
				'test'       => 1,
				'expected'   => ['b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => 'hello world, great', 'b' => 'hello, great'],
				'operator'   =>  'maxwords',
				'test'       => 1,
				'expected'   => ['b'],
				'split'      => ','
			],

			// DATE EQUALS
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => 'invalid date'],
				'operator'   =>  'date ==',
				'test'       => '2345-01-01',
				'expected'   => ['a', 'b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => date('Y-m-d'), 'b' => '02.01.2345'],
				'operator'   =>  'date ==',
				'test'       => 'today',
				'expected'   => ['a'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '02.01.2345'],
				'operator'   =>  'date ==',
				'test'       => 'invalid date',
				'expected'   => [],
				'split'      => false
			],

			// DATE NOT EQUALS
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => 'invalid date'],
				'operator'   =>  'date !=',
				'test'       => '2345-01-01',
				'expected'   => ['c'],
				'split'      => false
			],
			[
				'attributes' => ['a' => date('Y-m-d'), 'b' => '02.01.2345'],
				'operator'   =>  'date !=',
				'test'       => 'today',
				'expected'   => ['b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '02.01.2345'],
				'operator'   =>  'date !=',
				'test'       => 'invalid date',
				'expected'   => [],
				'split'      => false
			],

			// DATE MORE
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => 'invalid date'],
				'operator'   =>  'date >',
				'test'       => '2345-01-01',
				'expected'   => ['c'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => 'invalid date'],
				'operator'   =>  'date >',
				'test'       => 'invalid date',
				'expected'   => [],
				'split'      => false
			],

			// DATE MIN
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => '03.01.2345', 'e' => 'invalid date'],
				'operator'   =>  'date >=',
				'test'       => '2345-01-02',
				'expected'   => ['c', 'd'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => '03.01.2345', 'e' => 'invalid date'],
				'operator'   =>  'date >=',
				'test'       => 'invalid date',
				'expected'   => [],
				'split'      => false
			],

			// DATE LESS
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => 'invalid date'],
				'operator'   =>  'date <',
				'test'       => '2345-01-02',
				'expected'   => ['a', 'b'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => 'invalid date'],
				'operator'   =>  'date <',
				'test'       => 'invalid date',
				'expected'   => [],
				'split'      => false
			],

			// DATE MAX
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => '03.01.2345', 'e' => 'invalid date'],
				'operator'   =>  'date <=',
				'test'       => '2345-01-02',
				'expected'   => ['a', 'b', 'c'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => '03.01.2345', 'e' => 'invalid date'],
				'operator'   =>  'date <=',
				'test'       => 'invalid date',
				'expected'   => [],
				'split'      => false
			],

			// DATE BETWEEN
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => '31.12.2344', 'e' => 'invalid date'],
				'operator'   =>  'date between',
				'test'       => ['2345-01-01', '2345-01-05'],
				'expected'   => ['a', 'b', 'c'],
				'split'      => false
			],
			[
				'attributes' => ['a' => '2345-01-01', 'b' => '01.01.2345', 'c' => '02.01.2345', 'd' => '31.12.2344', 'e' => 'invalid date'],
				'operator'   =>  'date ..',
				'test'       => ['2345-01-01', '2345-01-05'],
				'expected'   => ['a', 'b', 'c'],
				'split'      => false
			]
		];
	}

	#[DataProvider('filterDataProvider')]
	public function testFilter(array $attributes, string $operator, string|bool|int|float|array $test, array $expected, bool|string $split)
	{
		$data = [];

		foreach ($attributes as $attributeKey => $attributeValue) {
			$data[$attributeKey] = [
				'attribute' => $attributeValue
			];
		}

		$collection = new Collection($data);
		$result     = $collection->filter('attribute', $operator, $test, $split);

		$this->assertSame($expected, $result->keys(), $operator);
	}

	public function testNot()
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$expected = [
			'two' => 'zwei',
		];

		$result = $collection->not('one', 'three');
		$this->assertInstanceOf(Collection::class, $result);
		$this->assertSame($expected, $result->data());
	}
}
