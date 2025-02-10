<?php

namespace Kirby\Form\Field;

class LineFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('line');

		$this->assertSame('line', $field->type());
		$this->assertSame('line', $field->name());
		$this->assertFalse($field->save());
	}
}
