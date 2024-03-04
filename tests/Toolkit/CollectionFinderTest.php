<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass \Kirby\Toolkit\Collection
 */
class CollectionFinderTest extends TestCase
{
	/**
	 * @covers ::findBy
	 */
	public function testFindBy()
	{
		$collection = new Collection([
			[
				'name' => 'Bastian',
				'email' => 'bastian@getkirby.com'
			],
			[
				'name' => 'Nico',
				'email' => 'nico@getkirby.com'
			]
		]);

		$this->assertSame([
			'name' => 'Bastian',
			'email' => 'bastian@getkirby.com'
		], $collection->findBy('email', 'bastian@getkirby.com'));
	}

	/**
	 * @covers ::find
	 */
	public function testFindKey()
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame('zwei', $collection->find('two'));
	}
}
