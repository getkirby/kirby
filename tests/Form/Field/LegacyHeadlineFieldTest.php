<?php

namespace Kirby\Form\Field;

class LegacyHeadlineFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('legacy-headline');

		$this->assertSame('legacy-headline', $field->type());
		$this->assertSame('legacy-headline', $field->name());
		$this->assertNull($field->value());
		$this->assertNull($field->label());
		$this->assertFalse($field->save());
	}
}
