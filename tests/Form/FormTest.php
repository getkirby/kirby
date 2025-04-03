<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Form\Field\ExceptionField;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Form\Form
 */
class FormTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.Form';

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

	/**
	 * @covers ::content
	 */
	public function testContent()
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

	/**
	 * @covers ::content
	 * @covers ::data
	 */
	public function testContentAndDataFromUnsaveableFields()
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

	/**
	 * @covers ::data
	 */
	public function testDataWithoutFields()
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

	/**
	 * @covers ::data
	 */
	public function testDataFromUnsaveableFields()
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

	/**
	 * @covers ::data
	 */
	public function testDataFromNestedFields()
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

	/**
	 * @covers ::data
	 * @covers ::values
	 */
	public function testDataWithCorrectFieldOrder()
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

	/**
	 * @covers ::data
	 * @covers ::values
	 */
	public function testDataWithStrictMode()
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

	/**
	 * @covers ::data
	 * @covers ::values
	 */
	public function testDataWithUntranslatedFields()
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

	/**
	 * @covers ::errors
	 * @covers ::isInvalid
	 * @covers ::isValid
	 */
	public function testErrors()
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

	/**
	 * @covers ::__construct
	 */
	public function testExceptionField()
	{
		$form = new Form([
			'fields' => [
				'test' => [
					'type'  => 'does-not-exist',
					'model' => $this->model
				]
			]
		]);

		$this->assertInstanceOf(ExceptionField::class, $form->fields()->first());
	}

	/**
	 * @covers ::for
	 */
	public function testForFileWithoutBlueprint()
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

	/**
	 * @covers ::for
	 */
	public function testForPage()
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

	/**
	 * @covers ::for
	 */
	public function testForPageWithClosureValues()
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

	/**
	 * @covers ::for
	 */
	public function testForPageWithoutMerge()
	{
		$page = new Page([
			'slug' => 'test',
			'content' => [
				'title' => 'Test',
				'date'  => '2012-12-12'
			],
		]);

		$form = Form::for(
			model: $page,
			props: [
				'values' => [
					'title' => 'Updated Title',
				],
			],
			merge: false
		);

		$values   = $form->values();
		$expected = [
			'title' => 'Updated Title'
		];

		$this->assertSame($expected, $values, 'The date field should not be present');
	}

	/**
	 * @covers ::strings
	 */
	public function testStrings()
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

	/**
	 * @covers ::toArray
	 */
	public function testToArray()
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

	/**
	 * @covers ::toFormValues
	 */
	public function testToFormValues()
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

	/**
	 * @covers ::toStoredValues
	 */
	public function testToStoredValues()
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

	/**
	 * @covers ::values
	 */
	public function testValuesWithoutFields()
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
