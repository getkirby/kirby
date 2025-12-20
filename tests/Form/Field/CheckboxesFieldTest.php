<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CheckboxesField::class)]
class CheckboxesFieldTest extends TestCase
{
	public function testBatch(): void
	{
		$field = $this->field('checkboxes');
		$this->assertFalse($field->batch());

		$field = $this->field('checkboxes', [
			'batch' => true
		]);
		$this->assertTrue($field->batch());
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

	public function testIsValid(): void
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'value'    => null,
			'required' => true
		]);
		$this->assertFalse($field->isValid());

		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'required' => true,
			'value'    => 'a'
		]);
		$this->assertTrue($field->isValid());
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

	public function testProps(): void
	{
		$field = $this->field('checkboxes');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus' => false,
			'batch'     => false,
			'columns'   => 1,
			'disabled'  => false,
			'help'      => null,
			'hidden'    => false,
			'label'     => 'Checkboxes',
			'max'       => null,
			'min'       => null,
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

	public function testRequired(): void
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
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

	public function testToFormValue(): void
	{
		$field = $this->field('checkboxes');
		$this->assertSame([], $field->toFormValue());

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

	public function testToStoredValue(): void
	{
		$field = $this->field('checkboxes');

		$field->fill(['a', 'b', 'c']);
		$this->assertSame('a, b, c', $field->toStoredvalue());
	}
}
