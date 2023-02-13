<?php

namespace Kirby\Form\Fields;

class WriterFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('writer');

		$this->assertSame('writer', $field->type());
		$this->assertSame('writer', $field->name());
		$this->assertSame([1, 2, 3, 4, 5, 6], $field->headings());
		$this->assertFalse($field->inline());
		$this->assertTrue($field->marks());
		$this->assertNull($field->nodes());
		$this->assertTrue($field->save());
	}

	public function testValueSanitized()
	{
		$field = $this->field('writer', [
			'value' => 'This is a <strong>test</strong><script>alert("Hacked")</script> with <em>formatting</em>'
		]);

		$this->assertSame('This is a <strong>test</strong> with <em>formatting</em>', $field->value());
	}

	public function testValueTrimmed()
	{
		$field = $this->field('writer', [
			'value' => 'test '
		]);

		$this->assertSame('test', $field->value());
	}
}
