<?php

namespace Kirby\Toolkit;

class MockObject
{
	public function __construct(
		protected string $value
	) {
	}

	public function value(): string
	{
		return $this->value;
	}
}

class MockObjectString extends MockObject
{
	public function __toString(): string
	{
		return (string)$this->value;
	}
}

class CollectionSorterTest extends TestCase
{
	public function testSort(): void
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
				'name' => 'nico',
				'role' => 'support',
				'color' => 'blue'
			],
			[
				'name'  => 'Sonja',
				'role'  => 'support',
				'color' => 'red'
			]
		]);

		$sorted = $collection->sort('name', 'asc');
		$this->assertSame('Bastian', $sorted->nth(0)['name']);
		$this->assertSame('green', $sorted->nth(1)['color']);
		$this->assertSame('blue', $sorted->nth(2)['color']);
		$this->assertSame('Sonja', $sorted->nth(3)['name']);

		$sorted = $collection->sort('name', 'desc');
		$this->assertSame('Bastian', $sorted->last()['name']);
		$this->assertSame('Sonja', $sorted->first()['name']);

		$sorted = $collection->sort('name', 'asc', 'color', SORT_ASC);
		$this->assertSame('blue', $sorted->nth(1)['color']);
		$this->assertSame('green', $sorted->nth(2)['color']);

		$sorted = $collection->sort('name', 'asc', 'color', SORT_DESC);
		$this->assertSame('green', $sorted->nth(1)['color']);
		$this->assertSame('blue', $sorted->nth(2)['color']);
	}

	public function testSortFlags(): void
	{
		$collection = new Collection([
			['name' => 'img12.png'],
			['name' => 'img10.png'],
			['name' => 'img2.png'],
			['name' => 'img1.png']
		]);

		$sorted = $collection->sort('name', 'asc', SORT_REGULAR);
		$this->assertSame('img1.png', $sorted->nth(0)['name']);
		$this->assertSame('img10.png', $sorted->nth(1)['name']);
		$this->assertSame('img12.png', $sorted->nth(2)['name']);
		$this->assertSame('img2.png', $sorted->nth(3)['name']);

		$sorted = $collection->sort('name', SORT_NATURAL);
		$this->assertSame('img1.png', $sorted->nth(0)['name']);
		$this->assertSame('img2.png', $sorted->nth(1)['name']);
		$this->assertSame('img10.png', $sorted->nth(2)['name']);
		$this->assertSame('img12.png', $sorted->nth(3)['name']);

		$sorted = $collection->sort('name', SORT_NATURAL, 'desc');
		$this->assertSame('img12.png', $sorted->nth(0)['name']);
		$this->assertSame('img10.png', $sorted->nth(1)['name']);
		$this->assertSame('img2.png', $sorted->nth(2)['name']);
		$this->assertSame('img1.png', $sorted->nth(3)['name']);
	}

	public function testSortCases(): void
	{
		$collection = new Collection([
			['name' => 'a'],
			['name' => 'c'],
			['name' => 'A'],
			['name' => 'b']
		]);

		$sorted = $collection->sort('name', 'asc');
		$this->assertSame('A', $sorted->nth(0)['name']);
		$this->assertSame('a', $sorted->nth(1)['name']);
		$this->assertSame('b', $sorted->nth(2)['name']);
		$this->assertSame('c', $sorted->nth(3)['name']);
	}

	public function testSortIntegers(): void
	{
		$collection = new Collection([
			['number' => 12],
			['number' => 1],
			['number' => 10],
			['number' => 2]
		]);

		$sorted = $collection->sort('number', 'asc');
		$this->assertSame(1, $sorted->nth(0)['number']);
		$this->assertSame(2, $sorted->nth(1)['number']);
		$this->assertSame(10, $sorted->nth(2)['number']);
		$this->assertSame(12, $sorted->nth(3)['number']);
	}

	public function testSortZeros(): void
	{
		$collection = new Collection([
			[
				'title'  => '1',
				'number' => 0
			],
			[
				'title'  => '2',
				'number' => 0
			],
			[
				'title'  => '3',
				'number' => 0
			],
			[
				'title'  => '4',
				'number' => 0
			]
		]);

		$sorted = $collection->sort('number', 'asc');
		$this->assertSame('1', $sorted->nth(0)['title']);
		$this->assertSame('2', $sorted->nth(1)['title']);
		$this->assertSame('3', $sorted->nth(2)['title']);
		$this->assertSame('4', $sorted->nth(3)['title']);
	}

	public function testSortCallable(): void
	{
		$collection = new Collection([
			[
				'title' => 'Second Article',
				'date'  => '2019-10-01 10:04',
				'tags'  => 'test'
			],
			[
				'title' => 'First Article',
				'date'  => '2018-31-12 00:00',
				'tags'  => 'test'
			],
			[
				'title' => 'Third Article',
				'date'  => '01.10.2019 10:05',
				'tags'  => 'test'
			]
		]);

		$sorted = $collection->sort(function ($value) {
			$this->assertSame('test', $value['tags']);

			return strtotime($value['date']);
		}, 'asc');
		$this->assertSame('First Article', $sorted->nth(0)['title']);
		$this->assertSame('Second Article', $sorted->nth(1)['title']);
		$this->assertSame('Third Article', $sorted->nth(2)['title']);

		$sorted = $collection->sort(function ($value) {
			$this->assertSame('test', $value['tags']);

			return strtotime($value['date']);
		}, 'desc');
		$this->assertSame('Third Article', $sorted->nth(0)['title']);
		$this->assertSame('Second Article', $sorted->nth(1)['title']);
		$this->assertSame('First Article', $sorted->nth(2)['title']);
	}

	public function testSortEmpty(): void
	{
		$collection = new Collection();
		$this->assertSame($collection, $collection->sort());
	}

	public function testSortObjects(): void
	{
		$bastian = new MockObjectString('Bastian');
		$nico    = new MockObjectString('Nico');
		$sonja   = new MockObjectString('Sonja');

		$collection = new Collection([
			['name' => $nico],
			['name' => $bastian],
			['name' => $sonja]
		]);

		$sorted = $collection->sort('name', 'asc');
		$this->assertSame($bastian, $sorted->nth(0)['name']);
		$this->assertSame($nico, $sorted->nth(1)['name']);
		$this->assertSame($sonja, $sorted->nth(2)['name']);
	}

	public function testSortNoRecursiveDependencyError(): void
	{
		// arrays; shouldn't be a problem
		$collection = new Collection([
			['name' => 'img1.png'],
			['name' => 'img2.png'],
			['name' => 'img1.png']
		]);

		$sorted = $collection->sort('name', 'asc');
		$this->assertSame('img1.png', $sorted->nth(0)['name']);
		$this->assertSame('img1.png', $sorted->nth(1)['name']);
		$this->assertSame('img2.png', $sorted->nth(2)['name']);

		// objects with a __toString() method
		$bastian = new MockObjectString('Bastian');
		$nico    = new MockObjectString('Nico');
		$sonja   = new MockObjectString('Sonja');

		$collection = new Collection([
			'nico'     => $nico,
			'bastian1' => $bastian,
			'sonja'    => $sonja,
			'bastian2' => $bastian
		]);
		$sorted = $collection->sort('value', 'asc');
		$this->assertSame($bastian, $sorted->nth(0));
		$this->assertSame($bastian, $sorted->nth(1));
		$this->assertSame($nico, $sorted->nth(2));
		$this->assertSame($sonja, $sorted->nth(3));

		// objects without a __toString() method
		$bastian = new MockObject('Bastian');
		$nico    = new MockObject('Nico');
		$sonja   = new MockObject('Sonja');

		$collection = new Collection([
			'nico'     => $nico,
			'bastian1' => $bastian,
			'sonja'    => $sonja,
			'bastian2' => $bastian
		]);
		$sorted = $collection->sort('value', 'asc');
		$this->assertSame($bastian, $sorted->nth(0));
		$this->assertSame($bastian, $sorted->nth(1));
		$this->assertSame($nico, $sorted->nth(2));
		$this->assertSame($sonja, $sorted->nth(3));
	}

	public function testFlip(): void
	{
		$collection = new Collection([
			['name' => 'img12.png'],
			['name' => 'img10.png'],
			['name' => 'img2.png']
		]);

		$this->assertSame('img12.png', $collection->nth(0)['name']);
		$this->assertSame('img10.png', $collection->nth(1)['name']);
		$this->assertSame('img2.png', $collection->nth(2)['name']);

		$flipped = $collection->flip();
		$this->assertSame('img2.png', $flipped->nth(0)['name']);
		$this->assertSame('img10.png', $flipped->nth(1)['name']);
		$this->assertSame('img12.png', $flipped->nth(2)['name']);
	}

	public function testShuffle(): void
	{
		$collection = new Collection([
			['name' => 'img12.png'],
			['name' => 'img10.png'],
			['name' => 'img2.png']
		]);

		$shuffled = $collection->shuffle();
		$this->assertCount(3, $shuffled);
	}
}
