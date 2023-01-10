<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
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

		$this->assertEquals('structure', $field->type());
		$this->assertEquals('structure', $field->name());
		$this->assertEquals(null, $field->limit());
		$this->assertTrue(is_array($field->fields()));
		$this->assertEquals([], $field->value());
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

		$this->assertEquals($expected, $field->data());
		$this->assertEquals('a, b', $field->data()[0]['tags']);
		$this->assertEquals(['a', 'b'], $field->value()[0]['tags']);
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
				'label' => 'a',
				'mobile' => true // the first column should be automatically kept on mobile
			],
			'b' => [
				'type' => 'text',
				'label' => 'b',
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
				'type' => 'text',
				'label' => 'b',
				'mobile' => true,
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

		$this->assertEquals(['camelcase'], array_keys($field->columns()));
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
		$this->assertEquals(2, $field->min());
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
		$this->assertEquals(1, $field->max());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testNestedStructures()
	{
		$field = $this->field('structure', [
			'model'  => 'test',
			'name'   => 'mothers',
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

		$this->assertEquals($value, $field->value());
		$this->assertEquals($value, $field->data());

		// empty mother form
		$motherForm = $field->form();
		$data       = $motherForm->data();

		$expected = [
			'name'     => null,
			'children' => []
		];

		unset($data['uuid']);

		$this->assertEquals($expected, $data);

		// filled mother form
		$motherForm = $field->form($value[0]);
		$expected   = $value[0];

		$this->assertEquals($expected, $motherForm->data());

		$childrenField = $motherForm->fields()->children();

		$this->assertEquals('structure', $childrenField->type());
		$this->assertEquals('test', $childrenField->model());

		// empty children form
		$childrenForm = $childrenField->form();

		$this->assertEquals(null, $childrenForm->data()['name']);

		// filled children form
		$childrenForm = $childrenField->form([
			'name' => 'Test'
		]);

		$this->assertEquals('Test', $childrenForm->data()['name']);

		// children name field
		$childrenNameField = $childrenField->form()->fields()->name();

		$this->assertEquals('text', $childrenNameField->type());
		$this->assertEquals('test', $childrenNameField->model());
		$this->assertEquals(null, $childrenNameField->data());
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

		$this->assertTrue(is_float($field->data()[0]['number']));
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

		$this->assertEquals('Test', $field->empty());
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

		$this->assertEquals('Test', $field->empty());
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

		$this->assertEquals('A', $field->data(true)[0]['a']);
		$this->assertEquals('B', $field->data(true)[0]['b']);
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
		$this->assertEquals(1, $field->min());
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
}
