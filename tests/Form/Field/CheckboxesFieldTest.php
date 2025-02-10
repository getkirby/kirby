<?php

namespace Kirby\Form\Field;

class CheckboxesFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('checkboxes');

		$this->assertSame('checkboxes', $field->type());
		$this->assertSame('checkboxes', $field->name());
		$this->assertSame([], $field->value());
		$this->assertSame([], $field->options());
		$this->assertTrue($field->save());
	}

	public function testValue()
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

	public function testEmptyValue()
	{
		$field = $this->field('checkboxes');

		$this->assertSame([], $field->value());
	}

	public function testDefaultValueWithInvalidOptions()
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

	public function testStringConversion()
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

	public function testIgnoreInvalidOptions()
	{
		$field = $this->field('checkboxes', [
			'options' => [
				'a',
				'b',
				'c'
			],
			'value' => 'a, b, d'
		]);

		$this->assertSame(['a', 'b'], $field->value());
	}

	public function testMin()
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

	public function testMax()
	{
		$field = $this->field('checkboxes', [
			'value'   => 'a, b',
			'options' => ['a', 'b', 'c'],
			'max'     => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testRequiredProps()
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testRequiredInvalid()
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'value'    => null,
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid()
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'required' => true,
			'value'    => 'a'
		]);

		$this->assertTrue($field->isValid());
	}
}
