<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StructureField::class)]
class StructureFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('structure', []);

		$props = $field->props();

		// makes it easier to compare the arrays
		ksort($props);

		$expected = [
			'autofocus' => false,
			'batch'     => false,
			'columns'   => [],
			'disabled'  => false,
			'duplicate' => true,
			'empty'     => null,
			'fields'    => [],
			'help'      => null,
			'hidden'    => false,
			'label'     => 'Structure',
			'limit'     => null,
			'max'       => null,
			'min'       => null,
			'name'      => 'structure',
			'prepend'   => false,
			'required'  => false,
			'saveable'  => true,
			'sortBy'    => null,
			'sortable'  => true,
			'translate' => true,
			'type'      => 'structure',
			'when'      => null,
			'width'     => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testReset(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'test' => [
					'type' => 'text'
				],
			],
		]);

		$field->fill($value = [
			[
				'test' => 'Test A'
			],
			[
				'test' => 'Test B'
			]
		]);

		$this->assertSame($value, $field->toFormValue());

		$field->reset();

		$this->assertSame([], $field->toFormValue());
	}


	public function testTagsFieldInStructure(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'tags' => [
					'label' => 'Tags',
					'type'  => 'tags'
				]
			],
			'value' => [
				[
					'tags' => 'a, b'
				]
			]
		]);

		$expected = [
			[
				'tags' => 'a, b'
			]
		];

		$this->assertSame($expected, $field->data());
		$this->assertSame('a, b', $field->data()[0]['tags']);
		$this->assertSame(['a', 'b'], $field->value()[0]['tags']);
	}

	public function testColumnsFromFields(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'a' => [
					'type' => 'text'
				],
				'b' => [
					'type' => 'text'
				]
			],
		]);

		$expected = [
			'a' => [
				'type' => 'text',
				'label' => 'A',
				'mobile' => true // the first column should be automatically kept on mobile
			],
			'b' => [
				'type' => 'text',
				'label' => 'B',
			],
		];

		$this->assertSame($expected, $field->columns());
	}

	public function testColumnsFromUnsaveableFields(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'a' => [
					'type' => 'text'
				],
				'b' => [
					'type' => 'info'
				]
			],
		]);

		$expected = [
			'a' => [
				'type' => 'text',
				'label' => 'A',
				'mobile' => true // the first column should be automatically kept on mobile
			],
		];

		$this->assertSame($expected, $field->columns());
	}

	public function testColumnsWithCustomMobileSetup(): void
	{
		$field = $this->field('structure', [
			'columns' => [
				'b' => [
					'mobile' => true
				]
			],
			'fields' => [
				'a' => [
					'type' => 'text'
				],
				'b' => [
					'type' => 'text'
				]
			],
		]);

		$expected = [
			'b' => [
				'mobile' => true,
				'type'   => 'text',
				'label'  => 'B',
			],
		];

		$this->assertSame($expected, $field->columns());
	}

	public function testColumnsWithI18nLabel(): void
	{
		$field = $this->field('structure', [
			'columns' => [
				'b' => [
					'label' => [
						'en' => 'Field B',
						'de' => 'Feld B'
					]
				]
			],
			'fields' => [
				'b' => [
					'type' => 'text'
				]
			],
		]);

		$expected = [
			'b' => [
				'label'  => 'Field B',
				'type'   => 'text',
				'mobile' => true
			],
		];

		$this->assertSame($expected, $field->columns());
	}

	public function testDuplicate(): void
	{
		$field = $this->field('structure');

		$this->assertTrue($field->duplicate());

		$field = $this->field('structure', [
			'duplicate' => false
		]);

		$this->assertFalse($field->duplicate());
	}

	public function testLowerCaseColumnsNames(): void
	{
		$field = $this->field('structure', [
			'columns' => [
				'camelCase' => true
			],
			'fields' => [
				'camelCase' => [
					'type' => 'text'
				]
			],
		]);

		$this->assertSame(['camelcase'], array_keys($field->columns()));
	}

	public function testMin(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'title' => [
					'type' => 'text'
				]
			],
			'value' => [
				['title' => 'a'],
			],
			'min' => 2
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(2, $field->min());
		$this->assertTrue($field->isRequired());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'title' => [
					'type' => 'text'
				]
			],
			'value' => [
				['title' => 'a'],
				['title' => 'b'],
			],
			'max' => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(1, $field->max());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testNestedStructures(): void
	{
		$model = new Page([
			'slug' => 'test'
		]);

		$field = $this->field('structure', [
			'name'   => 'mothers',
			'model'  => $model,
			'fields' => [
				'name' => [
					'type' => 'text',
				],
				'children' => [
					'type' => 'structure',
					'fields' => [
						'name' => [
							'type' => 'text'
						]
					]
				]
			],
			'value' => $value = [
				[
					'name' => 'Marge',
					'uuid' => 'my-marge',
					'children' => [
						[
							'name' => 'Lisa',
							'uuid' => 'my-lisa'
						],
						[
							'name' => 'Maggie',
							'uuid' => 'my-maggie'
						],
						[
							'name' => 'Bart',
							'uuid' => 'my-bart'
						]
					]
				]
			]
		]);

		$this->assertEquals($value, $field->value()); // cannot use strict assertion (array order)
		$this->assertEquals($value, $field->data()); // cannot use strict assertion (array order)

		// empty mother form
		$motherForm = $field->form();
		$data       = $motherForm->data();

		$expected = [
			'name'     => '',
			'children' => []
		];

		unset($data['uuid']);

		$this->assertSame($expected, $data);

		// filled mother form
		$motherForm = $field->form()->fill(input: $value[0], passthrough: true);
		$expected   = $value[0];

		$this->assertEquals($expected, $motherForm->data()); // cannot use strict assertion (array order)

		$childrenField = $motherForm->fields()->children();

		$this->assertSame('structure', $childrenField->type());
		$this->assertSame($model, $childrenField->model());

		// empty children form
		$childrenForm = $childrenField->form();

		$this->assertSame('', $childrenForm->data()['name']);

		// filled children form
		$childrenForm = $childrenField->form()->fill(input: ['name' => 'Test'], passthrough: true);

		$this->assertSame('Test', $childrenForm->data()['name']);

		// children name field
		$childrenNameField = $childrenField->form()->fields()->name();

		$this->assertSame('text', $childrenNameField->type());
		$this->assertSame($model, $childrenNameField->model());
		$this->assertSame('', $childrenNameField->data());
	}

	public function testFloatsWithNonUsLocale(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'number' => [
					'type' => 'number'
				]
			],
			'value' => [
				[
					'number' => 3.2
				]
			]
		]);

		$this->assertIsFloat($field->data()[0]['number']);
	}

	public function testEmpty(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			],
			'empty' => 'Test'
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testTranslatedEmpty(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			],
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testTranslate(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de',
				]
			]
		]);

		// we need an authenticated user to make sure
		// that the fields are not disabled by default
		$app->impersonate('kirby');

		$fields = [
			'a' => [
				'type' => 'text'
			],
			'b' => [
				'type' => 'text',
				'translate' => false
			]
		];

		$app->setCurrentLanguage('en');

		$field = $this->field('structure', [
			'fields' => $fields
		]);

		$props = $field->form()->fields()->toProps();

		$this->assertFalse($props['a']['disabled']);
		$this->assertFalse($props['b']['disabled']);

		$app->setCurrentLanguage('de');

		$field = $this->field('structure', [
			'fields' => $fields
		]);

		$props = $field->form()->fields()->toProps();

		$this->assertFalse($props['a']['disabled']);
		$this->assertTrue($props['b']['disabled']);
	}

	public function testDefault(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'a' => [
					'type' => 'text'
				],
				'b' => [
					'type' => 'text',
				]
			],
			'default' => [
				[
					'a' => 'A',
					'b' => 'B'
				]
			]
		]);

		$this->assertSame('A', $field->data(true)[0]['a']);
		$this->assertSame('B', $field->data(true)[0]['b']);
	}

	public function testRequiredProps(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'title' => [
					'type' => 'text'
				]
			],
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testRequiredInvalid(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'title' => [
					'type' => 'text'
				]
			],
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'title' => [
					'type' => 'text'
				]
			],
			'value' => [
				['title' => 'a'],
			],
			'required' => true
		]);

		$this->assertTrue($field->isValid());
	}

	public function testSubmitWithDisabledFieldAndDefaultValue(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'a' => [
					'type'     => 'text',
					'default'  => 'Default Title',
					'disabled' => true
				],
				'b' => [
					'type' => 'text'
				]
			],
		]);

		$field->submit([
			[
				'a' => 'A',
				'b' => 'B'
			]
		]);

		$value = $field->toStoredValue();

		$this->assertSame('A', $value[0]['a']);
		$this->assertSame('B', $value[0]['b']);
	}

	public function testValidationsInvalid(): void
	{
		$field = $this->field('structure', [
			'fields' => [
				'title' => [
					'type' => 'text',
					'required' => true
				]
			],
			'value' => [
				['title' => ''],
			]
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame([
			'structure' => 'There\'s an error on the "Title" field in row 1'
		], $field->errors());
	}
}
