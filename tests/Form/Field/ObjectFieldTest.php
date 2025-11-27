<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ObjectField::class)]
class ObjectFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('object');

		$props = $field->props();

		// makes it easier to compare the arrays
		ksort($props);

		$expected = [
			'autofocus' => false,
			'default'   => null,
			'disabled'  => false,
			'empty'     => null,
			'fields'    => [],
			'help'      => null,
			'hidden'    => false,
			'label'     => 'Object',
			'name'      => 'object',
			'required'  => false,
			'saveable'  => true,
			'translate' => true,
			'type'      => 'object',
			'when'      => null,
			'width'     => '1/1',
		];

		$this->assertSame($expected, $props);
	}

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

		$this->assertSame([], $field->data());
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
