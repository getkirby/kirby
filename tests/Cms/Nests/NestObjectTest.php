<?php

namespace Kirby\Cms;

use Kirby\Content\Field;
use PHPUnit\Framework\TestCase;

class NestObjectTest extends TestCase
{
	public function testToArray()
	{
		$o = new NestObject($expected = [
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame($expected, $o->toArray());
	}

	public function testToArrayWithFields()
	{
		$o = new NestObject([
			'a' => new Field(null, 'a', 'A'),
			'b' => new Field(null, 'a', 'B')
		]);

		$expected = [
			'a' => 'A',
			'b' => 'B'
		];

		$this->assertSame($expected, $o->toArray());
	}

	public function testToArrayWithNestedObjects()
	{
		$o = new NestObject([
			'user' => new NestObject([
				'name' => 'Peter'
			])
		]);

		$expected = [
			'user' => [
				'name' => 'Peter'
			]
		];

		$this->assertSame($expected, $o->toArray());
	}
}
