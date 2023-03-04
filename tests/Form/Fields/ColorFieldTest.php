<?php

namespace Kirby\Form\Fields;

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
		$field = $this->field('color', [
			'options' => ['a', 'b', 'c'],
		]);

		$this->assertSame([
			['value' => 'a', 'text' => null],
			['value' => 'b', 'text' => null],
			['value' => 'c', 'text' => null]
		], $field->options());

		$field = $this->field('color', [
			'options' => ['Color a' => 'a', 'Color b' => 'b', 'Color c' => 'c'],
		]);

		$this->assertSame([
			['value' => 'a', 'text' => 'Color a'],
			['value' => 'b', 'text' => 'Color b'],
			['value' => 'c', 'text' => 'Color c']
		], $field->options());
	}
}
