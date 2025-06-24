<?php

namespace Kirby\Form\Field;

class TelFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('tel');

		$this->assertSame('tel', $field->type());
		$this->assertSame('tel', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame('phone', $field->icon());
		$this->assertNull($field->placeholder());
		$this->assertNull($field->counter());
		$this->assertSame('tel', $field->autocomplete());
		$this->assertTrue($field->save());
	}
}
