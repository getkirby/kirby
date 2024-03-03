<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;

class ColorFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('color');

		$this->assertSame('color', $field->type());
		$this->assertSame('color', $field->name());
		$this->assertNull($field->value());
		$this->assertFalse($field->alpha());
		$this->assertSame('hex', $field->format());
		$this->assertSame('picker', $field->mode());
		$this->assertTrue($field->save());
	}

	public function testEmptyColor()
	{
		$field = $this->field('color', [
			'value' => null
		]);

		$this->assertNull($field->value());
		$this->assertNull($field->toString());
	}

	public function testDefault()
	{
		$field = $this->field('color', [
			'default' => '#fff',
		]);

		$this->assertSame('#fff', $field->default());
	}

	public function testFormatInvalid()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->field('color', ['format' => 'foo']);
	}

	public function testModeInvalid()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->field('color', ['mode' => 'foo']);
	}

	public function testOptions()
	{
		// Only values
		$field = $this->field('color', [
			'options' => ['#aaa', '#bbb', '#ccc'],
		]);

		$this->assertSame([
			['value' => '#aaa'],
			['value' => '#bbb'],
			['value' => '#ccc']
		], $field->options());

		// Value => Name
		$field = $this->field('color', [
			'options' => [
				'#aaa' => 'Color a',
				'#bbb' => 'Color b',
				'#ccc' => 'Color c'
			],
		]);

		$this->assertSame([
			['value' => '#aaa', 'text' => 'Color a'],
			['value' => '#bbb', 'text' => 'Color b'],
			['value' => '#ccc', 'text' => 'Color c']
		], $field->options());

		// Deprecated: name => value
		$field = $this->field('color', [
			'options' => [
				'Color a' => '#aaa',
				'Color b' => '#bbb',
				'Color c' => '#ccc'
			],
		]);

		$this->assertSame([
			['value' => '#aaa', 'text' => 'Color a'],
			['value' => '#bbb', 'text' => 'Color b'],
			['value' => '#ccc', 'text' => 'Color c']
		], $field->options());
	}

	public function testOptionsFromQuery()
	{
		// Only values
		$this->app = new App([
			'options' => [
				'foo' => ['#aaa', '#bbb', '#ccc']
			]
		]);

		$field = $this->field('color', [
			'options' => ['type' => 'query', 'query' => 'kirby.option("foo")'],
		]);

		$this->assertSame([
			['value' => '#aaa'],
			['value' => '#bbb'],
			['value' => '#ccc']
		], $field->options());

		// Value => Name
		$this->app = new App([
			'options' => [
				'foo' => [
					'#aaa' => 'Color a',
					'#bbb' => 'Color b',
					'#ccc' => 'Color c'
				]
			]
		]);

		$field = $this->field('color', [
			'options' => ['type' => 'query', 'query' => 'kirby.option("foo")'],
		]);

		$this->assertSame([
			['value' => '#aaa', 'text' => 'Color a'],
			['value' => '#bbb', 'text' => 'Color b'],
			['value' => '#ccc', 'text' => 'Color c']
		], $field->options());

		// Deprecated: name => value
		$this->app = new App([
			'options' => [
				'foo' => [
					'Color a' => '#aaa',
					'Color b' => '#bbb',
					'Color c' => '#ccc'
				],
			]
		]);

		$field = $this->field('color', [
			'options' => ['type' => 'query', 'query' => 'kirby.option("foo")'],
		]);

		$this->assertSame([
			['value' => '#aaa', 'text' => 'Color a'],
			['value' => '#bbb', 'text' => 'Color b'],
			['value' => '#ccc', 'text' => 'Color c']
		], $field->options());
	}
}
