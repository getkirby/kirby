<?php

namespace Kirby\Toolkit;

class CollectionFinderTest extends TestCase
{
	public function testFindBy(): void
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

	public function testFindKey(): void
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei'
		]);

		$this->assertSame('zwei', $collection->find('two'));
	}
}
