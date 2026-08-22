<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Form\Field\InfoField;
use Kirby\Form\Field\InputField;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Form::class)]
class FormTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Form.Form';

	protected ModelWithContent $model;

	protected function setUp(): void
	{
		$this->setUpSingleLanguage([
			'children' => [
				[
					'slug' => 'test'
				]
			]
		]);

		$this->model = $this->app->page('test');
		$this->setUpTmp();
	}

	protected function tearDown(): void
	{
		App::destroy();
		$this->tearDownTmp();
	}

	public function testDefaults(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'type'    => 'text',
					'default' => 'Test Value'
				]
			]
		);

		$this->assertSame(['test' => 'Test Value'], $form->defaults());
	}

	public function testErrors(): void
	{
		$form = new Form(
			fields: [
				'a' => [
					'label' => 'Email',
					'type'  => 'email',
				],
				'b' => [
					'label' => 'Url',
					'type'  => 'url',
				]
			],
			model: $this->model
		);

		$form->fill(input: [
			'a' => 'A',
			'b' => 'B',
		]);

		$this->assertTrue($form->isInvalid());
		$this->assertFalse($form->isValid());

		$expected = [
			'a' => [
				'label' => 'Email',
				'message' => [
					'email' => 'Please enter a valid email address'
				]
			],
			'b' => [
				'label' => 'Url',
				'message' => [
					'url' => 'Please enter a valid URL'
				]
			]
		];

		$this->assertSame($expected, $form->errors());

		// check for a correct cached array
		$this->assertSame($expected, $form->errors());
	}

	public function testFieldError(): void
	{
		// an invalid field does not take down the whole form,
		// but is replaced by an info field with the error message
		$form = new Form(
			fields: [
				'test' => [
					'type'  => 'does-not-exist',
					'model' => $this->model
				]
			]
		);

		$field = $form->fields()->get('test');

		$this->assertInstanceOf(InfoField::class, $field);
		$this->assertSame(
			'<p>Field "test": The field type "does-not-exist" does not exist</p>',
			(string)$field->text()
		);
	}

	public function testFill(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'type' => 'text',
				]
			]
		);

		$response = $form->fill([
			'test' => 'Test Value'
		]);

		$this->assertSame($form, $response);
		$this->assertSame(['test' => 'Test Value'], $response->toFormValues());
	}

	public function testFillWithDefaults(): void
	{
		$form = new Form(
			fields: [
				'a' => [
					'type'    => 'text',
					'default' => 'A'
				],
				'b' => [
					'type'    => 'text',
					'default' => 'B'
				]
			]
		);

		$response = $form->fill(
			input:    ['b' => 'Custom B'],
			defaults: true
		);

		$this->assertSame($form, $response);
		$this->assertSame(['a' => 'A', 'b' => 'Custom B'], $form->toFormValues());
	}

	public function testForFileWithoutBlueprint(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->model,
			'content'  => []
		]);

		$form = Form::for($file);
		$form->fill(input: ['a' => 'A', 'b' => 'B']);

		$this->assertSame(['a' => 'A', 'b' => 'B'], $form->toStoredValues());
	}

	public function testForPage(): void
	{
		$page = new Page([
			'slug' => 'test',
			'content' => [
				'title' => 'Test',
				'date'  => '2012-12-12'
			],
			'blueprint' => [
				'title' => 'Test',
				'name' => 'test',
				'fields' => [
					'date' => [
						'type' => 'date'
					]
				]
			]
		]);

		$form = Form::for($page);
		$form->fill(input: [
			'title' => 'Updated Title',
			'date'  => null
		]);

		$values = $form->toFormValues();

		// the title must always be transfered, even if not in the blueprint
		$this->assertSame('Updated Title', $values['title']);

		// empty fields should be actually empty
		$this->assertSame('', $values['date']);
	}

	public function testForPageWithClosureValues(): void
	{
		$page = new Page([
			'slug' => 'test',
			'content' => [
				'a' => 'A'
			]
		]);

		$form = Form::for($page);
		$form->fill(input: [
			'a' => fn ($value) => $value . 'A',
			'b' => fn ($value) => $value . 'B'
		]);

		$values = $form->toFormValues();

		$this->assertSame('AA', $values['a']);
		$this->assertSame('B', $values['b']);
	}

	public function testLanguage(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'type' => 'text',
				]
			]
		);

		$this->assertInstanceOf(Language::class, $form->language());
	}

	public function testPassthrough(): void
	{
		$form = new Form();

		$response = $form->passthrough([
			'test' => 'Test Value'
		]);

		$this->assertSame($form, $response);
		$this->assertSame(['test' => 'Test Value'], $response->passthrough());
		$this->assertSame(['test' => 'Test Value'], $response->toFormValues());
	}

	public function testReset(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'type' => 'text',
				]
			]
		);

		$this->assertSame(['test' => ''], $form->toFormValues());

		$form->fill([
			'test' => 'Test Value'
		]);

		$this->assertSame(['test' => 'Test Value'], $form->toFormValues());

		$response = $form->reset();

		$this->assertSame($form, $response);
		$this->assertSame(['test' => ''], $form->toFormValues());
	}

	public function testSubmit(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'type' => 'text',
				]
			]
		);

		$response = $form->submit([
			'test' => 'Test Value'
		]);

		$this->assertSame($form, $response);
		$this->assertSame(['test' => 'Test Value'], $response->toFormValues());
	}

	public function testToArray(): void
	{
		$form = new Form(
			fields: [
				'a' => [
					'label' => 'A',
					'type'  => 'text',
				],
				'b' => [
					'label' => 'B',
					'type'  => 'text',
				]
			],
			model: $this->model
		);

		$form->fill(input: [
			'a' => 'A',
			'b' => 'B',
		]);

		$this->assertSame([], $form->toArray()['errors']);
		$this->assertArrayHasKey('a', $form->toArray()['fields']);
		$this->assertArrayHasKey('b', $form->toArray()['fields']);
		$this->assertCount(2, $form->toArray()['fields']);
		$this->assertFalse($form->toArray()['invalid']);
	}

	public function testToArrayInvalid(): void
	{
		$form = new Form(
			fields: [
				'a' => [
					'label'    => 'A',
					'type'     => 'text',
					'required' => true,
				]
			],
			model: $this->model
		);

		$array = $form->toArray();

		$this->assertArrayHasKey('a', $array['errors']);
		$this->assertTrue($array['invalid']);
	}

	public function testToFormValues(): void
	{
		$form = new Form(
			fields: [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				]
			]
		);

		$form->fill(input: $values = [
			'a' => 'A',
			'b' => 'B',
		]);

		$this->assertSame($values, $form->toFormValues());
	}

	public function testToFormValuesWithoutFields(): void
	{
		$form = new Form();
		$form->fill(input: $values = [
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame($values, $form->toFormValues());
	}

	public function testToFormValuesWithUntranslatedFields(): void
	{
		$this->setUpMultiLanguage();

		$this->model = new Page([
			'slug' => 'test',
			'blueprint' => [
				'fields' => [
					'a' => [
						'type' => 'text'
					],
					'b' => [
						'type' => 'text',
						'translate' => false
					]
				],
			]
		]);

		// default language
		$form = Form::for($this->model);
		$form->submit(input: [
			'a' => 'A',
			'b' => 'B'
		]);

		$expected = [
			'a' => 'A',
			'b' => 'B'
		];

		$this->assertSame($expected, $form->toFormValues());

		// secondary language
		$form = Form::for($this->model, language: 'de');
		$form->submit(input: [
			'a' => 'A',
			'b' => 'B'
		]);

		$expected = [
			'a' => 'A',
			'b' => ''
		];

		$this->assertSame($expected, $form->toFormValues());
	}

	public function testToProps(): void
	{
		$form = new Form(
			fields: [
				'test' => [
					'label' => 'Test',
					'type'  => 'text',
				],
			]
		);

		$this->assertSame($form->fields()->toProps(), $form->toProps());
	}

	public function testToStoredValues(): void
	{
		$field = new class () extends InputField {
			protected mixed $value = null;

			public function toStoredValue(): mixed
			{
				return parent::toStoredValue() . ' stored';
			}
		};

		Field::$types['test'] = $field::class;

		$form = new Form(
			fields: [
				'a' => [
					'type' => 'test',
				],
				'b' => [
					'type' => 'test',
				]
			]
		);

		$form->fill(input: [
			'a' => 'A',
			'b' => 'B',
		]);

		$expected = [
			'a' => 'A stored',
			'b' => 'B stored'
		];

		$this->assertSame($expected, $form->toStoredValues());
	}

	public function testToStoredValuesFromNestedFields(): void
	{
		$form = new Form(
			fields: [
				'structure' => [
					'type'   => 'structure',
					'fields' => [
						'tags' => [
							'type'  => 'tags',
						]
					]
				]
			],
			model: $this->model
		);

		$form->fill(input: [
			'structure' => [
				[
					'tags' => 'a, b'
				]
			]
		]);

		$this->assertSame('a, b', $form->toStoredValues()['structure'][0]['tags']);
	}

	public function testToStoredValuesFromUnsaveableFields(): void
	{
		$form = new Form(
			fields: [
				'info' => [
					'type' => 'info',
				]
			],
			model: $this->model
		);

		$form->fill(input: [
			'info' => 'Yay'
		]);

		$this->assertCount(0, $form->toStoredValues());
		$this->assertArrayNotHasKey('info', $form->toStoredValues());
	}

	public function testToStoredValuesWithCorrectFieldOrder(): void
	{
		$form = new Form(
			fields: [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				]
			],
			model: $this->model
		);

		$form->fill(input: [
			'c' => 'C',
			'b' => 'B',
			'a' => 'A',
		]);

		$form->submit(input: [
			'b' => 'B modified'
		]);

		$this->assertTrue(['a' => 'A', 'b' => 'B modified', 'c' => 'C'] === $form->toStoredValues());
		$this->assertTrue(['a' => 'A', 'b' => 'B modified', 'c' => 'C'] === $form->toFormValues());
	}

	public function testToStoredValuesWithoutFields(): void
	{
		$form = new Form();
		$form->fill(input: $values = [
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame($values, $form->toStoredValues());
	}

	public function testToStoredValuesWithoutPassthrough(): void
	{
		$form = new Form(
			fields: [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				]
			],
			model: $this->model
		);

		$form->fill(
			input: [
				'b' => 'B',
				'a' => 'A'
			],
			passthrough: false
		);

		$form->submit(
			input: [
				'c' => 'C'
			],
			passthrough: false
		);

		$this->assertTrue(['a' => 'A', 'b' => 'B'] === $form->toStoredValues());
		$this->assertTrue(['a' => 'A', 'b' => 'B'] === $form->toFormValues());
	}
}
