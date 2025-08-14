<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Form::class)]
class FormTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Form.Form';

	protected ModelWithContent $model;

	public function setUp(): void
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

	public function tearDown(): void
	{
		App::destroy();
		$this->tearDownTmp();
	}

	public function testContent(): void
	{
		$form = new Form([
			'fields' => [],
			'values' => $values = [
				'a' => 'A',
				'b' => 'B'
			]
		]);

		$this->assertSame($values, $form->content());
	}

	public function testContentAndDataFromUnsaveableFields(): void
	{
		$form = new Form([
			'fields' => [
				'info' => [
					'type' => 'info',
				]
			],
			'model' => $this->model,
			'values' => [
				'info' => 'Yay'
			]
		]);

		$this->assertCount(0, $form->content());
		$this->assertArrayNotHasKey('info', $form->content());
		$this->assertCount(1, $form->data());
		$this->assertArrayHasKey('info', $form->data());
	}

	public function testDataWithoutFields(): void
	{
		$form = new Form([
			'fields' => [],
			'values' => $values = [
				'a' => 'A',
				'b' => 'B'
			]
		]);

		$this->assertSame($values, $form->data());
	}

	public function testDataFromUnsaveableFields(): void
	{
		$form = new Form([
			'fields' => [
				'info' => [
					'type' => 'info',
				]
			],
			'model' => $this->model,
			'values' => [
				'info' => 'Yay'
			]
		]);

		$this->assertNull($form->data()['info']);
	}

	public function testDataFromNestedFields(): void
	{
		$form = new Form([
			'fields' => [
				'structure' => [
					'type'   => 'structure',
					'fields' => [
						'tags' => [
							'type'  => 'tags',
						]
					]
				]
			],
			'model' => $this->model,
			'values' => $values = [
				'structure' => [
					[
						'tags' => 'a, b'
					]
				]
			]
		]);

		$this->assertSame('a, b', $form->data()['structure'][0]['tags']);
	}

	public function testDataWithCorrectFieldOrder(): void
	{
		$form = new Form([
			'fields' => [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				]
			],
			'input' => [
				'b' => 'B modified'
			],
			'model' => $this->model,
			'values' => [
				'c' => 'C',
				'b' => 'B',
				'a' => 'A',
			],
		]);

		$this->assertTrue(['a' => 'A', 'b' => 'B modified', 'c' => 'C'] === $form->data());
		$this->assertTrue(['a' => 'A', 'b' => 'B modified', 'c' => 'C'] === $form->values());
	}

	public function testDataWithStrictMode(): void
	{
		$form = new Form([
			'fields' => [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				]
			],
			'input' => [
				'c' => 'C'
			],
			'model' => $this->model,
			'strict' => true,
			'values' => [
				'b' => 'B',
				'a' => 'A'
			],
		]);

		$this->assertTrue(['a' => 'A', 'b' => 'B'] === $form->data());
		$this->assertTrue(['a' => 'A', 'b' => 'B'] === $form->values());
	}

	public function testDataWithUntranslatedFields(): void
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
		$form = Form::for($this->model, [
			'input' => [
				'a' => 'A',
				'b' => 'B'
			]
		]);

		$expected = [
			'a' => 'A',
			'b' => 'B'
		];

		$this->assertSame($expected, $form->values());

		// secondary language
		$form = Form::for($this->model, [
			'language' => 'de',
			'input' => [
				'a' => 'A',
				'b' => 'B'
			]
		]);

		$expected = [
			'a' => 'A',
			'b' => ''
		];

		$this->assertSame($expected, $form->values());
	}

	public function testDefaults(): void
	{
		$form = new Form([
			'fields' => [
				'test' => [
					'type' => 'text',
					'default' => 'Test Value'
				]
			]
		]);

		$this->assertSame(['test' => 'Test Value'], $form->defaults());
	}

	public function testErrors(): void
	{
		$form = new Form([
			'fields' => [
				'a' => [
					'label' => 'Email',
					'type' => 'email',
				],
				'b' => [
					'label' => 'Url',
					'type' => 'url',
				]
			],
			'model' => $this->model,
			'values' => [
				'a' => 'A',
				'b' => 'B',
			]
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

	public function testFieldException(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Field "test": The field type "does-not-exist" does not exist');

		new Form([
			'fields' => [
				'test' => [
					'type'  => 'does-not-exist',
					'model' => $this->model
				]
			]
		]);
	}

	public function testFill(): void
	{
		$form = new Form([
			'fields' => [
				'test' => [
					'type' => 'text',
				]
			],
		]);

		$response = $form->fill([
			'test' => 'Test Value'
		]);

		$this->assertSame($form, $response);
		$this->assertSame(['test' => 'Test Value'], $response->toFormValues());
	}

	public function testForFileWithoutBlueprint(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->model,
			'content'  => []
		]);

		$form = Form::for($file, [
			'values' => ['a' => 'A', 'b' => 'B']
		]);

		$this->assertSame(['a' => 'A', 'b' => 'B'], $form->data());
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

		$form = Form::for($page, [
			'values' => [
				'title' => 'Updated Title',
				'date'  => null
			]
		]);

		$values = $form->values();

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

		$form = Form::for($page, [
			'values' => [
				'a' => fn ($value) => $value . 'A',
				'b' => fn ($value) => $value . 'B'
			]
		]);

		$values = $form->values();

		$this->assertSame('AA', $values['a']);
		$this->assertSame('B', $values['b']);
	}

	public function testLanguage(): void
	{
		$form = new Form([
			'fields' => [
				'test' => [
					'type' => 'text',
				]
			],
		]);

		$this->assertInstanceOf(Language::class, $form->language());
	}

	public function testPassthrough(): void
	{
		$form = new Form([]);

		$response = $form->passthrough([
			'test' => 'Test Value'
		]);

		$this->assertSame($form, $response);
		$this->assertSame(['test' => 'Test Value'], $response->passthrough());
		$this->assertSame(['test' => 'Test Value'], $response->toFormValues());
	}

	public function testReset(): void
	{
		$form = new Form([
			'fields' => [
				'test' => [
					'type' => 'text',
				]
			],
		]);

		$this->assertSame(['test' => ''], $form->toFormValues());

		$form->fill([
			'test' => 'Test Value'
		]);

		$this->assertSame(['test' => 'Test Value'], $form->toFormValues());

		$response = $form->reset();

		$this->assertSame($form, $response);
		$this->assertSame(['test' => ''], $form->toFormValues());
	}

	public function testStrings(): void
	{
		$form = new Form([
			'fields' => [],
			'values' => [
				'a' => 'A',
				'b' => 'B',
				'c' => [
					'd' => 'D',
					'e' => 'E'
				]
			]
		]);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B',
			'c' => "d: D\ne: E\n"
		], $form->strings());
	}

	public function testSubmit(): void
	{
		$form = new Form([
			'fields' => [
				'test' => [
					'type' => 'text',
				]
			],
		]);

		$response = $form->submit([
			'test' => 'Test Value'
		]);

		$this->assertSame($form, $response);
		$this->assertSame(['test' => 'Test Value'], $response->toFormValues());
	}

	public function testToArray(): void
	{
		$form = new Form([
			'fields' => [
				'a' => [
					'label' => 'A',
					'type'  => 'text',
				],
				'b' => [
					'label' => 'B',
					'type'  => 'text',
				]
			],
			'model' => $this->model,
			'values' => [
				'a' => 'A',
				'b' => 'B',
			]
		]);

		$this->assertSame([], $form->toArray()['errors']);
		$this->assertArrayHasKey('a', $form->toArray()['fields']);
		$this->assertArrayHasKey('b', $form->toArray()['fields']);
		$this->assertCount(2, $form->toArray()['fields']);
		$this->assertFalse($form->toArray()['invalid']);
	}

	public function testToFormValues(): void
	{
		$form = new Form([
			'fields' => [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				]
			],
			'values' => $values = [
				'a' => 'A',
				'b' => 'B',
			]
		]);

		$this->assertSame($values, $form->toFormValues());
	}

	public function testToProps(): void
	{
		$form = new Form([
			'fields' => [
				'test' => [
					'label' => 'Test',
					'type'  => 'text',
				],
			]
		]);

		$this->assertSame($form->fields()->toProps(), $form->toProps());
	}

	public function testToStoredValues(): void
	{
		Field::$types['test'] = [
			'save' => function ($value) {
				return $value . ' stored';
			}
		];

		$form = new Form([
			'fields' => [
				'a' => [
					'type' => 'test',
				],
				'b' => [
					'type' => 'test',
				]
			],
			'values' => [
				'a' => 'A',
				'b' => 'B',
			]
		]);

		$expected = [
			'a' => 'A stored',
			'b' => 'B stored'
		];

		$this->assertSame($expected, $form->toStoredValues());
	}

	public function testValuesWithoutFields(): void
	{
		$form = new Form([
			'fields' => [],
			'values' => $values = [
				'a' => 'A',
				'b' => 'B'
			]
		]);

		$this->assertSame($values, $form->values());
	}
}
