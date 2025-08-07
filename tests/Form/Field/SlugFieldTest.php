<?php

namespace Kirby\Form\Field;

class SlugFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('slug');

		$this->assertSame('slug', $field->type());
		$this->assertSame('slug', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame('url', $field->icon());
		$this->assertSame('', $field->allow());
		$this->assertNull($field->path());
		$this->assertNull($field->sync());
		$this->assertNull($field->placeholder());
		$this->assertNull($field->counter());
		$this->assertFalse($field->wizard());
		$this->assertTrue($field->save());
	}
}
