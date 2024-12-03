<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Field;

class StructureFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('structure', [
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			]
		]);

		$this->assertSame('structure', $field->type());
		$this->assertSame('structure', $field->name());
		$this->assertNull($field->limit());
		$this->assertIsArray($field->fields());
		$this->assertSame([], $field->value());
		$this->assertTrue($field->save());
	}

	public function testTagsFieldInStructure()
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

	public function testColumnsFromFields()
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

	public function testColumnsWithCustomMobileSetup()
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

	public function testColumnsWithI18nLabel()
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

	public function testLowerCaseColumnsNames()
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

	public function testMin()
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
		$this->assertTrue($field->required());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax()
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

	public function testNestedStructures()
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
		$motherForm = $field->form($value[0]);
		$expected   = $value[0];

		$this->assertEquals($expected, $motherForm->data()); // cannot use strict assertion (array order)

		$childrenField = $motherForm->fields()->children();

		$this->assertSame('structure', $childrenField->type());
		$this->assertSame($model, $childrenField->model());

		// empty children form
		$childrenForm = $childrenField->form();

		$this->assertSame('', $childrenForm->data()['name']);

		// filled children form
		$childrenForm = $childrenField->form([
			'name' => 'Test'
		]);

		$this->assertSame('Test', $childrenForm->data()['name']);

		// children name field
		$childrenNameField = $childrenField->form()->fields()->name();

		$this->assertSame('text', $childrenNameField->type());
		$this->assertSame($model, $childrenNameField->model());
		$this->assertSame('', $childrenNameField->data());
	}

	public function testFloatsWithNonUsLocale()
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

	public function testEmpty()
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

	public function testTranslatedEmpty()
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

	public function testTranslate()
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

		$field = $this->field('structure', [
			'fields' => [
				'a' => [
					'type' => 'text'
				],
				'b' => [
					'type' => 'text',
					'translate' => false
				]
			]
		]);

		$app->setCurrentLanguage('en');

		$this->assertFalse($field->form()->fields()->a()->disabled());
		$this->assertFalse($field->form()->fields()->b()->disabled());

		$app->setCurrentLanguage('de');

		$this->assertFalse($field->form()->fields()->a()->disabled());
		$this->assertTrue($field->form()->fields()->b()->disabled());
	}

	public function testDefault()
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

	public function testRequiredProps()
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

	public function testRequiredInvalid()
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

	public function testRequiredValid()
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

	public function testValidationsInvalid()
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
