<?php

namespace Kirby\Form\Field;

class ListFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('list');

		$this->assertSame('list', $field->type());
		$this->assertSame('list', $field->name());
		$this->assertSame('', $field->value());
		$this->assertNull($field->label());
		$this->assertNull($field->text());
		$this->assertTrue($field->save());
	}
}
