<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;

class ColorFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('color');

		$this->assertSame('color', $field->type());
		$this->assertSame('color', $field->name());
		$this->assertFalse($field->alpha());
		$this->assertSame('hex', $field->format());
		$this->assertSame('picker', $field->mode());
		$this->assertTrue($field->hasValue());
	}

	public function testEmptyColor(): void
	{
		$field = $this->field('color', [
			'value' => null
		]);

		$this->assertNull($field->toFormValue());
		$this->assertNull($field->toStoredValue());
	}

	public function testDefault(): void
	{
		$field = $this->field('color', [
			'default' => '#fff',
		]);

		$this->assertSame('#fff', $field->default());
	}

	public function testReset(): void
	{
		$field = $this->field('color');
		$field->fill('#efefef');

		$this->assertSame('#efefef', $field->toFormValue());

		$field->reset();

		$this->assertSame('', $field->toFormValue());
	}

	public function testFormatInvalid(): void
	{
		$field = $this->field('color', ['format' => 'foo']);
		$this->assertSame('hex', $field->format());
	}

	public function testModeInvalid(): void
	{
		$field = $this->field('color', ['mode' => 'foo']);
		$this->assertSame('picker', $field->mode());
	}

	public function testOptions(): void
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
	}

	public function testOptionsFromQuery(): void
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
	}
}
