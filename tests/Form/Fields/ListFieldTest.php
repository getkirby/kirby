<?php

namespace Kirby\Form\Fields;

class ListFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('list');

		$this->assertSame('list', $field->type());
		$this->assertSame('list', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame('List', $field->label());
		$this->assertNull($field->text());
		$this->assertTrue($field->save());
	}
}
