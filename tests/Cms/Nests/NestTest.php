<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class NestTest extends TestCase
{
	public function testCreateScalar(): void
	{
		$n = Nest::create($expected = 'a');

		$this->assertInstanceOf(Field::class, $n);
		$this->assertEquals($expected, $n); // cannot use strict assertion (string conversion)
	}

	public function testCreateEmptyCollection(): void
	{
		$n = Nest::create([]);
		$this->assertInstanceOf(NestCollection::class, $n);
		$this->assertCount(0, $n);
	}

	public function testCreateObject(): void
	{
		$n = Nest::create($expected = [
			'a' => 'A',
			'b' => 2,
			'c' => false
		]);

		$this->assertInstanceOf(NestObject::class, $n);
		$this->assertSame($expected, $n->toArray());
	}

	public function testCreateCollection(): void
	{
		$n = Nest::create($expected = ['A', 2, false]);

		$this->assertInstanceOf(NestCollection::class, $n);
		$this->assertSame('A', $n->first()->value());
		$this->assertFalse($n->last()->value());
	}
}
