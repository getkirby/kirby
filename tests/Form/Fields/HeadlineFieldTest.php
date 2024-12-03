<?php

namespace Kirby\Form\Fields;

class HeadlineFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('headline');

		$this->assertSame('headline', $field->type());
		$this->assertSame('headline', $field->name());
		$this->assertNull($field->value());
		$this->assertSame('Headline', $field->label());
		$this->assertFalse($field->save());
	}
}
