<?php

namespace Kirby\Form\Fields;

class RangeFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('range');

		$this->assertSame('range', $field->type());
		$this->assertSame('range', $field->name());
		$this->assertSame(null, $field->value());
		$this->assertSame(null, $field->default());
		$this->assertSame(null, $field->min());
		$this->assertSame(100.0, $field->max());
		$this->assertSame(null, $field->step());
		$this->assertTrue($field->tooltip());
		$this->assertTrue($field->save());
	}

	public function testMin()
	{
		$field = $this->field('range', [
			'value' => 1,
			'min'   => 2
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax()
	{
		$field = $this->field('range', [
			'value' => 1,
			'max'   => 0
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}
}
