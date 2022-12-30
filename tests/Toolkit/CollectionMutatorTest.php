<?php

namespace Kirby\Toolkit;

class CollectionMutatorTest extends TestCase
{
	public function testData()
	{
		$collection = new Collection();

		$this->assertSame([], $collection->data());

		$collection->data([
			'three' => 'drei'
		]);
		$this->assertSame([
			'three' => 'drei'
		], $collection->data());

		$collection->data([
			'one' => 'eins',
			'two' => 'zwei'
		]);
		$this->assertSame([
			'one' => 'eins',
			'two' => 'zwei'
		], $collection->data());
	}

	public function testEmpty()
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame([
			'one' => 'eins',
			'two' => 'zwei'
		], $collection->data());

		$this->assertSame([], $collection->empty()->data());
	}

	public function testSet()
	{
		$collection = new Collection();
		$this->assertNull($collection->one);
		$this->assertNull($collection->two);

		$collection->one = 'eins';
		$this->assertSame('eins', $collection->one);

		$collection->set('two', 'zwei');
		$this->assertSame('zwei', $collection->two);

		$collection->set([
			'three' => 'drei'
		]);
		$this->assertSame('drei', $collection->three);
	}

	public function testAppend()
	{
		$collection = new Collection([
			'one' => 'eins'
		]);

		$this->assertSame('eins', $collection->last());

		$collection->append('two', 'zwei');
		$this->assertSame('zwei', $collection->last());
	}

	public function testPrepend()
	{
		$collection = new Collection([
			'one' => 'eins'
		]);

		$this->assertSame('eins', $collection->first());

		$collection->prepend('zero', 'null');
		$this->assertSame('null', $collection->zero());
	}

	public function testExtend()
	{
		$collection = new Collection([
			'one' => 'eins'
		]);

		$result = $collection->extend([
			'two' => 'zwei'
		]);

		$this->assertSame('eins', $result->one());
		$this->assertSame('zwei', $result->two());
	}

	public function testRemove()
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame('zwei', $collection->two());
		$collection->remove('two');
		$this->assertNull($collection->two());
	}

	public function testUnset()
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame('zwei', $collection->two());
		unset($collection->two);
		$this->assertNull($collection->two());
	}

	public function testMap()
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame('zwei', $collection->two());
		$collection->map(function ($item) {
			return $item . '-ish';
		});
		$this->assertSame('zwei-ish', $collection->two());
	}

	public function testPluck()
	{
		$collection = new Collection([
			[
				'username' => 'homer',
			],
			[
				'username' => 'marge',
			]
		]);

		$this->assertSame(['homer', 'marge'], $collection->pluck('username'));
	}

	public function testPluckAndSplit()
	{
		$collection = new Collection([
			[
				'simpsons' => 'homer, marge',
			],
			[
				'simpsons' => 'maggie, bart, lisa',
			]
		]);

		$expected = [
			'homer', 'marge', 'maggie', 'bart', 'lisa'
		];

		$this->assertSame($expected, $collection->pluck('simpsons', ', '));
	}

	public function testPluckUnique()
	{
		$collection = new Collection([
			[
				'user' => 'homer',
			],
			[
				'user' => 'homer',
			],
			[
				'user' => 'marge',
			]
		]);

		$expected = ['homer', 'marge'];

		$this->assertSame($expected, $collection->pluck('user', null, true));
	}
}
