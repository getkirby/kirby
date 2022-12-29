<?php

namespace Kirby\Form\Fields;

class HiddenFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('hidden');

		$this->assertSame('hidden', $field->type());
		$this->assertSame('hidden', $field->name());
		$this->assertSame(null, $field->value());
		$this->assertTrue($field->save());
	}
}
