<?php

namespace Kirby\Form\Field;

class MultiselectFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('multiselect');

		$this->assertSame('multiselect', $field->type());
		$this->assertSame('multiselect', $field->name());
		$this->assertSame([], $field->value());
		$this->assertSame([], $field->default());
		$this->assertSame([], $field->options());
		$this->assertNull($field->min());
		$this->assertNull($field->max());
		$this->assertSame(',', $field->separator());
		$this->assertSame('checklist', $field->icon());
		$this->assertNull($field->counter());
		$this->assertTrue($field->search());
		$this->assertFalse($field->sort());
		$this->assertTrue($field->save());
	}

	public function testMin()
	{
		$field = $this->field('multiselect', [
			'value'   => 'a',
			'options' => ['a', 'b', 'c'],
			'min'     => 2
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax()
	{
		$field = $this->field('multiselect', [
			'value'   => 'a, b',
			'options' => ['a', 'b', 'c'],
			'max'     => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testSanitizeOptions()
	{
		$field = $this->field('multiselect', [
			'value'   => 'a, b',
			'options' => ['b', 'c'],
		]);

		$this->assertCount(1, $field->value());
		$this->assertArrayHasKey(0, $field->value());
	}
}
