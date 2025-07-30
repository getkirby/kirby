<?php

namespace Kirby\Form\ArrayField;

class ObjectFieldTest extends TestCase
{
	public function testData(): void
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

	public function testDataWhenEmpty(): void
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

	public function testDefaultProps(): void
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
		$this->assertIsArray($field->fields());
		$this->assertSame('', $field->value());
		$this->assertTrue($field->save());
	}

	public function testDefaultValue(): void
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

	public function testErrors(): void
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

	public function testErrorsWhenEmpty(): void
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

	public function testFieldsMissing(): void
	{
		$field = $this->field('object', []);
		$this->assertSame([], $field->fields());
	}

	public function testTagsField(): void
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
