<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class NestCollectionTest extends TestCase
{
	public function testToArray(): void
	{
		$collection = new NestCollection([
			new NestObject([
				'name' => 'Peter'
			]),
			new NestObject([
				'name' => 'Paul'
			]),
			new NestObject([
				'name' => 'Mary'
			])
		]);

		$expected = [
			[
				'name' => 'Peter'
			],
			[
				'name' => 'Paul'
			],
			[
				'name' => 'Mary'
			]
		];

		$this->assertSame($expected, $collection->toArray());
	}
}
