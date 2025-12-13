<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HeadlineField::class)]
class HeadlineFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('headline');

		$this->assertSame('headline', $field->type());
		$this->assertSame('headline', $field->name());
		$this->assertSame('Headline', $field->label());
		$this->assertFalse($field->hasValue());
	}
}
