<?php

namespace Kirby\Form;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Form\Form
 */
class FormTest extends TestCase
{
	public function tearDown(): void
	{
		App::destroy();
	}

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
	 * @covers ::exceptionField
	 */
	public function testExceptionFieldDebug()
	{
		$exception = new Exception('This is an error');

		$app  = new App();
		$props = ['name' => 'test', 'model' => $app->site()];
		$field = Form::exceptionField($exception, $props)->toArray();
		$this->assertSame('info', $field['type']);
		$this->assertSame('Error in "test" field.', $field['label']);
		$this->assertSame('<p>This is an error</p>', $field['text']);
		$this->assertSame('negative', $field['theme']);

		$app   = $app->clone(['options' => ['debug' => true]]);
		$props = ['name' => 'test', 'model' => $app->site()];
		$field = Form::exceptionField($exception, $props)->toArray();
		$this->assertSame('info', $field['type']);
		$this->assertSame('Error in "test" field.', $field['label']);
		$this->assertStringContainsString('<p>This is an error in file:', $field['text']);
		$this->assertStringContainsString('tests/Form/FormTest.php line: 39</p>', $field['text']);
		$this->assertSame('negative', $field['theme']);
	}

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

	public function testDataFromUnsaveableFields()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$form = new Form([
			'fields' => [
				'info' => [
					'type' => 'info',
					'model' => $page
				]
			],
			'values' => [
				'info' => 'Yay'
			]
		]);

		$this->assertNull($form->data()['info']);
	}

	public function testDataFromNestedFields()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$form = new Form([
			'fields' => [
				'structure' => [
					'type'   => 'structure',
					'model' => $page,
					'fields' => [
						'tags' => [
							'type'  => 'tags',
							'model' => $page
						]
					]
				]
			],
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

	public function testInvalidFieldType()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$form = new Form([
			'fields' => [
				'test' => [
					'type'  => 'does-not-exist',
					'model' => $page
				]
			]
		]);

		$field = $form->fields()->first();

		$this->assertSame('info', $field->type());
		$this->assertSame('negative', $field->theme());
		$this->assertSame('Error in "test" field.', $field->label());
		$this->assertSame('<p>Field "test": The field type "does-not-exist" does not exist</p>', $field->text());
	}

	public function testFieldOrder()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$form = new Form([
			'fields' => [
				'a' => [
					'type'  => 'text',
					'model' => $page
				],
				'b' => [
					'type'  => 'text',
					'model' => $page
				]
			],
			'values' => [
				'c' => 'C',
				'b' => 'B',
				'a' => 'A',
			],
			'input' => [
				'b' => 'B modified'
			]
		]);

		$this->assertTrue(['a' => 'A', 'b' => 'B modified', 'c' => 'C'] === $form->values());
		$this->assertTrue(['a' => 'A', 'b' => 'B modified', 'c' => 'C'] === $form->data());
	}

	public function testStrictMode()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$form = new Form([
			'fields' => [
				'a' => [
					'type' => 'text',
					'model' => $page
				],
				'b' => [
					'type' => 'text',
					'model' => $page
				]
			],
			'values' => [
				'b' => 'B',
				'a' => 'A'
			],
			'input' => [
				'c' => 'C'
			],
			'strict' => true
		]);

		$this->assertTrue(['a' => 'A', 'b' => 'B'] === $form->values());
		$this->assertTrue(['a' => 'A', 'b' => 'B'] === $form->data());
	}

	public function testErrors()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$form = new Form([
			'fields' => [
				'a' => [
					'label' => 'Email',
					'type' => 'email',
					'model' => $page
				],
				'b' => [
					'label' => 'Url',
					'type' => 'url',
					'model' => $page
				]
			],
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

	public function testToArray()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$form = new Form([
			'fields' => [
				'a' => [
					'label' => 'A',
					'type' => 'text',
					'model' => $page
				],
				'b' => [
					'label' => 'B',
					'type' => 'text',
					'model' => $page
				]
			],
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

	public function testContentFromUnsaveableFields()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$page = new Page(['slug' => 'test']);
		$form = new Form([
			'fields' => [
				'info' => [
					'type' => 'info',
					'model' => $page
				]
			],
			'values' => [
				'info' => 'Yay'
			]
		]);

		$this->assertCount(0, $form->content());
		$this->assertArrayNotHasKey('info', $form->content());
		$this->assertCount(1, $form->data());
		$this->assertArrayHasKey('info', $form->data());
	}

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

	public function testPageForm()
	{
		App::instance();

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

	public function testPageFormWithClosures()
	{
		$page = new Page([
			'slug' => 'test',
			'content' => [
				'a' => 'A'
			]
		]);

		$form = Form::for($page, [
			'values' => [
				'a' => function ($value) {
					return $value . 'A';
				},
				'b' => function ($value) {
					return $value . 'B';
				},
			]
		]);

		$values = $form->values();

		$this->assertSame('AA', $values['a']);
		$this->assertSame('B', $values['b']);
	}

	public function testFileFormWithoutBlueprint()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$page = new Page([
			'slug' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => []
		]);

		$form = Form::for($file, [
			'values' => ['a' => 'A', 'b' => 'B']
		]);

		$this->assertSame(['a' => 'A', 'b' => 'B'], $form->data());
	}

	public function testUntranslatedFields()
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
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			]
		]);

		$page = new Page([
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
		$form = Form::for($page, [
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
		$form = Form::for($page, [
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
}
