<?php

namespace Kirby\Form;

use Kirby\Cms\Page;
use Kirby\TestCase;

class FieldsTest extends TestCase
{
	public function setUp(): void
	{
		Field::$types = [];
	}

	public function tearDown(): void
	{
		Field::$types = [];
	}

	public function testConstruct()
	{
		Field::$types = [
			'test' => []
		];

		$page   = new Page(['slug' => 'test']);
		$fields = new Fields([
			'a' => [
				'type' => 'test',
				'model' => $page
			],
			'b' => [
				'type' => 'test',
				'model' => $page
			],
		]);

		$this->assertSame('a', $fields->first()->name());
		$this->assertSame('b', $fields->last()->name());
	}
}
