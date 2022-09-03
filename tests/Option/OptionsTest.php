<?php

namespace Kirby\Option;

use Kirby\Field\TestCase;

/**
 * @covers \Kirby\Option\Options
 */
class OptionsTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$options = Options::factory(['a', 'b']);

		$this->assertSame('a', $options->first()->value);
		$this->assertSame('a', $options->first()->text->translations['*']);

		$this->assertSame('b', $options->last()->value);
		$this->assertSame('b', $options->last()->text->translations['*']);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructWithAssocArray()
	{
		$options = Options::factory([
			'a' => 'Option A',
			'b' => 'Option B'
		]);

		$this->assertSame('a', $options->first()->value);
		$this->assertSame('Option A', $options->first()->text->translations['*']);

		$this->assertSame('b', $options->last()->value);
		$this->assertSame('Option B', $options->last()->text->translations['*']);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructWithOptionArray()
	{
		$options = Options::factory([
			['value' => 'a', 'text' => 'Option A'],
			['value' => 'b', 'text' => 'Option B']
		]);

		$this->assertSame('a', $options->first()->value);
		$this->assertSame('Option A', $options->first()->text->translations['*']);

		$this->assertSame('b', $options->last()->value);
		$this->assertSame('Option B', $options->last()->text->translations['*']);
	}
}
