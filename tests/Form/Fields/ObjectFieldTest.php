<?php

namespace Kirby\Form\Fields;

class ObjectFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('object', [
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			]
		]);

		$this->assertSame('object', $field->type());
		$this->assertSame('object', $field->name());
		$this->assertTrue(is_array($field->fields()));
		$this->assertNull($field->value());
		$this->assertTrue($field->save());
	}

	public function testTagsField()
	{
		$field = $this->field('object', [
			'fields' => [
				'tags' => [
					'label' => 'Tags',
					'type'  => 'tags'
				]
			],
			'value' => [
				'tags' => 'a, b'
			]
		]);

		$this->assertSame(['a', 'b'], $field->value()['tags']);

		$expected = [
			'tags' => 'a, b'
		];

		$this->assertSame($expected, $field->data());
	}
}
