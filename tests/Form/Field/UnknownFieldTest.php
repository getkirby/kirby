<?php

namespace Kirby\Form\Field;

class UnknownFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = new UnknownField(name: 'unknown');

		$this->assertSame(['name' => 'unknown', 'hidden' => true], $field->props());
		$this->assertSame('unknown', $field->type());
		$this->assertSame('unknown', $field->name());
		$this->assertTrue($field->isHidden());
		$this->assertTrue($field->isSaveable());
	}
}
