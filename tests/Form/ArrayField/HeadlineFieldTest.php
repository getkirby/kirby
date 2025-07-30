<?php

namespace Kirby\Form\ArrayField;

class HeadlineFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('headline');

		$this->assertSame('headline', $field->type());
		$this->assertSame('headline', $field->name());
		$this->assertNull($field->value());
		$this->assertNull($field->label());
		$this->assertFalse($field->save());
	}
}
