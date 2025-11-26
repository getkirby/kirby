<?php

namespace Kirby\Form\Field;

class RadioFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('radio');

		$this->assertSame('radio', $field->type());
		$this->assertSame('radio', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame([], $field->options());
		$this->assertTrue($field->hasValue());
	}
}
