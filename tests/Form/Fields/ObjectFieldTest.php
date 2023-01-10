<?php

namespace Kirby\Form\Fields;

class ObjectFieldTest extends TestCase
{
	public function testData()
	{
		$field = $this->field('object', [
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			],
			'value' => $value = [
				'text' => 'test'
			]
		]);

		$this->assertSame($value, $field->data());
	}

	public function testDataWhenEmpty()
	{
		$field = $this->field('object', [
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			]
		]);

		$this->assertSame('', $field->data());
	}

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
		$this->assertSame('', $field->value());
		$this->assertTrue($field->save());
	}

	public function testDefaultValue()
	{
		$field = $this->field('object', [
			'fields' => [
				'text' => [
					'type' => 'text',
				]
			],
			'default' => [
				'text' => 'foo'
			]
		]);

		$expected = [
			'text' => 'foo'
		];

		$this->assertSame($expected, $field->default());
	}

	public function testErrors()
	{
		$field = $this->field('object', [
			'fields' => [
				'url' => [
					'type' => 'url',
					'minlength' => 20
				]
			],
			'value' => [
				'url' => 'bar'
			]
		]);

		$expected = [
			'object' =>
				'There’s an error in the "url" field:' . "\n" .
				'Please enter a longer value. (min. 20 characters)' . "\n" .
				'Please enter a valid URL'
		];

		$this->assertSame($expected, $field->errors());
	}

	public function testErrorsWhenEmpty()
	{
		$field = $this->field('object', [
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			]
		]);

		$this->assertSame([], $field->errors());
	}

	public function testFieldsMissing()
	{
		$this->expectException('Exception');
		$this->expectExceptionMessage('Please provide some fields for the object');

		$this->field('object', []);
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
