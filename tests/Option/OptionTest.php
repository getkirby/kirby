<?php

namespace Kirby\Option;

use Kirby\Cms\Page;
use Kirby\Field\TestCase;

/**
 * @coversDefaultClass \Kirby\Option\Option
 */
class OptionTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::id
	 */
	public function testConstruct()
	{
		// string
		$option = new Option('test');
		$this->assertSame('test', $option->value);
		$this->assertSame('test', $option->id());
		$this->assertSame('test', $option->text['en']);

		// int
		$option = new Option(1);
		$this->assertSame(1, $option->value);
		$this->assertSame(1, $option->id());
		$this->assertSame(1, $option->text['en']);

		// float
		$option = new Option(1.1);
		$this->assertSame(1.1, $option->value);
		$this->assertSame(1.1, $option->id());
		$this->assertSame(1.1, $option->text['en']);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryWithJustValue()
	{
		// string
		$option = Option::factory('test');
		$this->assertSame('test', $option->value);

		// int
		$option = Option::factory(1);
		$this->assertSame(1, $option->value);

		// float
		$option = Option::factory(1.0);
		$this->assertSame(1.0, $option->value);
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
		$this->assertSame('Test Option', $option->text['en']);

		// array
		$option = Option::factory([
			'value' => 'test',
			'text'  => [
				'de' => 'Test Option'
			]
		]);

		$this->assertSame('test', $option->value);
		$this->assertSame('Test Option', $option->text['de']);
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

		$model = new Page(['slug' => 'test']);
		$this->assertSame($expected, $option->render($model));
	}
}
