<?php

namespace Kirby\Form\Fields;

class GapFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('gap');

		$this->assertSame('gap', $field->type());
		$this->assertSame('gap', $field->name());
		$this->assertFalse($field->save());
	}
}
