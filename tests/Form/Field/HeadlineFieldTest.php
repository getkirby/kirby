<?php

namespace Kirby\Form\Field;

class HeadlineFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('headline');

		$this->assertSame('headline', $field->type());
		$this->assertSame('headline', $field->name());
		$this->assertNull($field->value());
		$this->assertSame('Headline', $field->label());
		$this->assertFalse($field->hasValue());
		$this->assertFalse($field->save());
	}
}
