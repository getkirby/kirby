<?php

namespace Kirby\Form\Field;

class CheckboxesFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('checkboxes');

		$this->assertSame('checkboxes', $field->type());
		$this->assertSame('checkboxes', $field->name());
		$this->assertSame([], $field->value());
		$this->assertSame([], $field->options());
		$this->assertTrue($field->save());
	}

	public function testValue(): void
	{
		$field = $this->field('checkboxes', [
			'value'   => 'a,b,c',
			'options' => $expected = [
				'a',
				'b',
				'c'
			]
		]);

		$this->assertSame($expected, $field->value());
	}

	public function testEmptyValue(): void
	{
		$field = $this->field('checkboxes');

		$this->assertSame([], $field->value());
	}

	public function testDefaultValueWithInvalidOptions(): void
	{
		$field = $this->field('checkboxes', [
			'default' => 'a,b,d',
			'options' => [
				'a',
				'b',
				'c'
			],
		]);

		$this->assertSame(['a', 'b'], $field->default());
		$this->assertSame('a, b', $field->data(true));
	}

	public function testFillWithEmptyValue(): void
	{
		$field = $this->field('checkboxes', [
			'options' => [
				'a',
				'b',
				'c'
			],
		]);

		$field->fill(['a', 'b']);

		$this->assertSame(['a', 'b'], $field->toFormValue());

		$field->fillWithEmptyValue();

		$this->assertSame([], $field->toFormValue());
	}

	public function testStringConversion(): void
	{
		$field = $this->field('checkboxes', [
			'options' => [
				'a',
				'b',
				'c'
			],
			'value' => 'a,b,c,d'
		]);

		$this->assertSame('a, b, c', $field->data());
	}

	public function testIgnoreInvalidOptions(): void
	{
		$field = $this->field('checkboxes', [
			'options' => [
				'a',
				'b',
				'c'
			],
			'value' => 'a, b, d'
		]);

		$this->assertSame('a, b', $field->toStoredValue());
	}

	public function testMin(): void
	{
		$field = $this->field('checkboxes', [
			'value'   => 'a',
			'options' => ['a', 'b', 'c'],
			'min'     => 2
		]);

		$this->assertTrue($field->required());
		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax(): void
	{
		$field = $this->field('checkboxes', [
			'value'   => 'a, b',
			'options' => ['a', 'b', 'c'],
			'max'     => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testRequiredProps(): void
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testRequiredInvalid(): void
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'value'    => null,
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid(): void
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'required' => true,
			'value'    => 'a'
		]);

		$this->assertTrue($field->isValid());
	}

	public function testBatch(): void
	{
		$field = $this->field('checkboxes');

		$this->assertFalse($field->batch());

		$field = $this->field('checkboxes', [
			'batch' => true
		]);

		$this->assertTrue($field->batch());
	}

}
