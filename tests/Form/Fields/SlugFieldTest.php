<?php

namespace Kirby\Form\Fields;

class SlugFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('slug');

		$this->assertSame('slug', $field->type());
		$this->assertSame('slug', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame('url', $field->icon());
		$this->assertSame('', $field->allow());
		$this->assertSame(null, $field->path());
		$this->assertSame(null, $field->sync());
		$this->assertSame(null, $field->placeholder());
		$this->assertSame(null, $field->counter());
		$this->assertSame(false, $field->wizard());
		$this->assertTrue($field->save());
	}
}
