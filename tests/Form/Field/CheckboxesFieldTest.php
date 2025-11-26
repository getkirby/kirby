<?php

namespace Kirby\Form\Field;

class CheckboxesFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('checkboxes');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus' => false,
			'batch'     => false,
			'columns'   => 1,
			'default'   => null,
			'disabled'  => false,
			'help'      => null,
			'hidden'    => false,
			'label'     => 'Checkboxes',
			'name'      => 'checkboxes',
			'options'   => [],
			'required'  => false,
			'saveable'  => true,
			'translate' => true,
			'type'      => 'checkboxes',
			'when'      => null,
			'width'     => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testValue(): void
	{
		$field = $this->field('checkboxes', [
			'options' => $expected = [
				'a',
				'b',
				'c'
			]
		]);

		$field->fill('a, b, c');
		$this->assertSame($expected, $field->toFormValue());
	}

	public function testEmptyValue(): void
	{
		$field = $this->field('checkboxes');
		$this->assertSame([], $field->toFormValue());
	}

	public function testReset(): void
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

		$field->reset();

		$this->assertSame([], $field->toFormValue());
	}

	public function testToStoredValue(): void
	{
		$field = $this->field('checkboxes');

		$field->fill(['a', 'b', 'c']);

		$this->assertSame('a, b, c', $field->toStoredvalue());
	}

	public function testMin(): void
	{
		$field = $this->field('checkboxes', [
			'value'   => 'a',
			'options' => ['a', 'b', 'c'],
			'min'     => 2
		]);

		$this->assertTrue($field->isRequired());
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

	public function testFillWithInvalidOption(): void
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c']
		]);

		$field->fill('c');

		$this->assertTrue($field->isValid());

		$field->fill('d');

		$this->assertFalse($field->isValid());
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
