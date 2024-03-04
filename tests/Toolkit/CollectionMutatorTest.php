<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass \Kirby\Toolkit\Collection
 */
class CollectionMutatorTest extends TestCase
{
	/**
	 * @covers ::data
	 */
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

	/**
	 * @covers ::empty
	 */
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

	/**
	 * @covers ::set
	 */
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

	/**
	 * @covers ::append
	 */
	public function testAppend()
	{
		$collection = new Collection([
			'one' => 'eins'
		]);

		$this->assertSame('eins', $collection->last());

		$collection->append('two', 'zwei');
		$this->assertSame('zwei', $collection->last());
	}

	/**
	 * @covers ::prepend
	 */
	public function testPrepend()
	{
		$collection = new Collection([
			'one' => 'eins'
		]);

		$this->assertSame('eins', $collection->first());

		$collection->prepend('zero', 'null');
		$this->assertSame('null', $collection->zero());
	}

	/**
	 * @covers ::extend
	 */
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

	/**
	 * @covers ::remove
	 */
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

	/**
	 * @covers ::__unset
	 */
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

	/**
	 * @covers ::map
	 */
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

	/**
	 * @covers ::pluck
	 */
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

	/**
	 * @covers ::pluck
	 */
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

	/**
	 * @covers ::pluck
	 */
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
