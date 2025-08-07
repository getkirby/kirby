<?php

namespace Kirby\Form\Field;

class GapFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('gap');

		$this->assertSame('gap', $field->type());
		$this->assertSame('gap', $field->name());
		$this->assertFalse($field->save());
	}
}
