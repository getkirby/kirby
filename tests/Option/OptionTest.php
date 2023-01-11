<?php

namespace Kirby\Option;

use Kirby\Field\TestCase;

/**
 * @coversDefaultClass \Kirby\Option\Option
 */
class OptionTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		// string
		$option = new Option('test');
		$this->assertSame('test', $option->value);
		$this->assertSame('test', $option->text->translations['en']);

		// int
		$option = new Option(1);
		$this->assertSame(1, $option->value);
		$this->assertSame(1, $option->text->translations['en']);

		// float
		$option = new Option(1.1);
		$this->assertSame(1.1, $option->value);
		$this->assertSame(1.1, $option->text->translations['en']);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryWithValueAndText()
	{
		// string
		$option = Option::factory([
			'value' => 'test',
			'text'  => 'Test Option'
		]);

		$this->assertSame('test', $option->value);
		$this->assertSame('Test Option', $option->text->translations['en']);

		// array
		$option = Option::factory([
			'value' => 'test',
			'text'  => [
				'de' => 'Test Option'
			]
		]);

		$this->assertSame('test', $option->value);
		$this->assertSame('Test Option', $option->text->translations['de']);
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$option = Option::factory([
			'value' => 'test',
			'text'  => 'Test Option',
			'info'  => '{{ page.slug }}'
		]);

		$expected = [
			'disabled' => false,
			'icon'     => null,
			'info'     => 'test',
			'text'     => 'Test Option',
			'value'    => 'test',
		];

		$this->assertSame($expected, $option->render($this->model()));
	}
}
