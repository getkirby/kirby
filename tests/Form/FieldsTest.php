<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\TestCase;
use Kirby\Cms\User;
use Kirby\Exception\FormValidationException;
use Kirby\Exception\NotFoundException;
use Kirby\Form\Field\BaseField;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Fields::class)]
class FieldsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Form.Fields';

	protected App $app;
	protected Page $model;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
		$this->model = new Page(['slug' => 'test']);
	}

	public function testConstruct(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
				],
				'b' => [
					'type'  => 'text',
				],
			],
			model: $this->model
		);

		$this->assertSame('a', $fields->first()->name());
		$this->assertSame($this->model, $fields->first()->model());
		$this->assertSame('b', $fields->last()->name());
		$this->assertSame($this->model, $fields->last()->model());
	}

	public function testConstructWithoutModel(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
				],
				'b' => [
					'type'  => 'text',
				],
			]
		);

		$this->assertSame($this->app->site(), $fields->first()->model());
		$this->assertSame($this->app->site(), $fields->last()->model());
	}

	public function testDefaults(): void
	{
		$fields = new Fields([
			'a' => [
				'default' => 'a',
				'type'    => 'text'
			],
			'b' => [
				'default' => 'b',
				'type'    => 'text'
			],
		], $this->model);

		$this->assertSame(['a' => 'a', 'b' => 'b'], $fields->defaults());
	}

	public function testErrors(): void
	{
		$fields = new Fields([
			'a' => [
				'label'    => 'A',
				'type'     => 'text',
				'required' => true
			],
			'b' => [
				'label'    => 'B',
				'type'      => 'text',
				'maxlength' => 3,
				'value'     => 'Too long'
			],
		], $this->model);

		$this->assertSame([
			'a' => [
				'label'   => 'A',
				'message' => [
					'required' => 'Please enter something'
				]
			],
			'b' => [
				'label'   => 'B',
				'message' => [
					'maxlength' => 'Please enter a shorter value. (max. 3 characters)'
				]
			]
		], $fields->errors());

		$fields->fill([
			'a' => 'A',
		]);

		$this->assertSame([
			'b' => [
				'label'   => 'B',
				'message' => [
					'maxlength' => 'Please enter a shorter value. (max. 3 characters)'
				]
			]
		], $fields->errors());
	}

	public function testErrorsWithoutErrors(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
			],
			'b' => [
				'type'  => 'text',
			],
		], $this->model);

		$this->assertSame([], $fields->errors());
	}

	public function testField(): void
	{
		$fields = new Fields([
			'test' => [
				'type'  => 'text',
			],
		], $this->model);

		$this->assertSame('test', $fields->field('test')->name());
	}

	public function testFieldWithMissingField(): void
	{
		$fields = new Fields([
			'test' => [
				'type'  => 'text',
			],
		], $this->model);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The field could not be found');

		$fields->field('missing');
	}


	public function testFill(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'A'
			],
			'b' => [
				'type'  => 'text',
				'value' => 'B'
			],
		], $this->model);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toArray(fn ($field) => $field->value()));

		$fields->fill($input = [
			'a' => 'A updated',
			'b' => 'B updated'
		]);

		$this->assertSame($input, $fields->toArray(fn ($field) => $field->value()));
	}

	public function testFillWithClosureValues(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A'
				],
			],
			model: $this->model
		);

		$fields->fill([
			'a' => fn ($value) => $value . ' updated'
		]);

		$this->assertSame([
			'a' => 'A updated'
		], $fields->toFormValues());
	}

	public function testFillWithNoValueField(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type' => 'info',
				],
				'b' => [
					'type' => 'text',
				]
			],
			model: $this->model
		);

		$fields->fill([
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame([
			'b' => 'B',
		], $fields->toFormValues());
	}

	public function testFillWithPassthrough(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type' => 'text',
				],
			],
			model: $this->model
		);

		$input = [
			'a' => 'A',
			'b' => 'B',
		];

		$fields->fill($input, passthrough: false);

		$this->assertSame([
			'a' => 'A',
		], $fields->toFormValues(), 'Unknown fields are not included');

		$fields->fill($input, passthrough: true);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toFormValues(), 'Unknown fields are included');
	}

	public function testFind(): void
	{
		Field::$types['test'] = [
			'methods' => [
				'form' => function () {
					return new Form([
						'fields' => [
							'child' => [
								'type'  => 'text',
							],
						],
						'model' => $this->model
					]);
				}
			]
		];

		$fields = new Fields([
			'mother' => [
				'type'  => 'test',
			],
		], $this->model);

		$this->assertSame('mother', $fields->find('mother')->name());
		$this->assertSame('child', $fields->find('mother+child')->name());
		$this->assertNull($fields->find('mother+missing-child'));
	}

	public function testFindInFieldClass(): void
	{
		$motherClass = new class () extends BaseField {
			public function form(): Form
			{
				return new Form([
					'fields' => [
						'child' => [
							'type' => 'text',
						],
					],
					'model' => $this->model
				]);
			}
		};

		$mother = new $motherClass(name: 'mother');
		$mother->setModel($this->model);

		$fields = new Fields([
			$mother
		]);

		$this->assertSame('mother', $fields->find('mother')->name());
		$this->assertSame('child', $fields->find('mother+child')->name());
	}

	public function testFindWhenFieldHasNoForm(): void
	{
		$fields = new Fields([
			'mother' => [
				'type'  => 'text',
			],
		], $this->model);

		$this->assertNull($fields->find('mother+child'));
	}

	public function testFor(): void
	{
		$this->model = new Page([
			'slug' => 'test',
			'blueprint' => [
				'fields' => [
					'a' => [
						'type' => 'text',
					],
					'b' => [
						'type' => 'text',
					]
				]
			]
		]);

		$fields = Fields::for($this->model);

		$this->assertTrue($fields->language()->isDefault());
		$this->assertCount(2, $fields);

		$this->assertSame('a', $fields->first()->name());
		$this->assertSame('b', $fields->last()->name());

		$this->assertSame($this->model, $fields->first()->model());
		$this->assertSame($this->model, $fields->last()->model());
	}

	public function testForWithLanguage(): void
	{
		$this->setUpMultiLanguage();

		$fields = Fields::for($this->model, 'de');

		$this->assertSame('de', $fields->language()->code());
	}

	public function testLanguage(): void
	{
		// no language passed = current language
		$fields = new Fields();
		$this->assertSame('en', $fields->language()->code());
		$this->assertTrue($fields->language()->isDefault());

		// language passed
		$language = new Language(['code' => 'de']);
		$fields = new Fields(fields: [], language: $language);
		$this->assertSame('de', $fields->language()->code());
		$this->assertFalse($fields->language()->isDefault());

		// language code passed
		$fields = new Fields(fields: [], language: 'en');
		$this->assertSame('en', $fields->language()->code());
	}

	public function testPassthrough(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'a'
			],
		], $this->model);

		$fields->passthrough([
			'b' => 'B',
		]);

		$this->assertSame([
			'a' => 'a',
			'b' => 'B'
		], $fields->toFormValues());
	}

	public function testPassthroughAsGetter(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'A'
			],
		], $this->model);

		$this->assertSame([], $fields->passthrough());

		$fields->passthrough([
			'a' => 'A', // should be ignored
			'b' => 'B',
		]);

		$this->assertSame([
			'b' => 'B'
		], $fields->passthrough());
	}


	public function testPassthroughWithExistingField(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'a'
			],
		], $this->model);

		$fields->passthrough([
			'a' => 'A', // should be ignored
			'b' => 'B',
		]);

		$this->assertSame([
			'a' => 'a',
			'b' => 'B'
		], $fields->toFormValues());
	}

	public function testPassthroughWithClosureValues(): void
	{
		$fields = new Fields([], $this->model);

		$fields->passthrough([
			'test' => 'Test', // should be ignored
		]);

		$this->assertSame([
			'test' => 'Test',
		], $fields->toFormValues());


		$fields->passthrough([
			'test' => fn ($value) => $value . ' updated'
		]);

		$this->assertSame([
			'test' => 'Test updated'
		], $fields->toFormValues());
	}

	public function testPassthroughWithUpperAndLowerCases(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'a'
			],
		], $this->model);

		$fields->passthrough([
			'A' => 'A', // should be ignored even with the wrong case
			'b' => 'B',
		]);

		$fields->passthrough([
			'B' => 'B changed' // should be stored with the lower case 'b' key
		]);

		$this->assertSame([
			'a' => 'a',
			'b' => 'B changed'
		], $fields->toFormValues());
	}

	public function testPassthroughWithExistingPassthroughValues(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'A'
			],
		], $this->model);

		// add passthrough values
		$fields->passthrough([
			'b' => 'B',
		]);

		// replace passthrough values
		$fields->passthrough([
			'c' => 'C'
		]);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B',
			'c' => 'C'
		], $fields->toFormValues());
	}

	public function testPassthroughWithFillAndSubmit(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
			],
		], $this->model);

		$fields->fill(
			input: [
				'a' => 'A',
				'b' => 'B'
			]
		);

		$fields->submit(
			input: [
				'c' => 'C',
			]
		);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B',
			'c' => 'C',
		], $fields->toFormValues());
	}

	public function testReset(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
			],
			'b' => [
				'type'  => 'text',
			],
			'c' => [
				'type' => 'entries'
			]
		], $this->model);

		$fields->fill([
			'a' => 'A',
			'b' => 'B',
			'c' => ['a', 'b', 'c']
		]);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B',
			'c' => ['a', 'b', 'c']
		], $fields->toFormValues());

		$fields->reset();

		$this->assertSame([
			'a' => '',
			'b' => '',
			'c' => []
		], $fields->toFormValues());
	}

	public function testResetStructureFields(): void
	{
		$fields = new Fields([
			'a' => [
				'type' => 'structure',
				'fields' => [
					'text' => [
						'type'  => 'text',
					],
				],
			],
		], $this->model);

		$fields->fill([
			'a' => $input = [
				[
					'text' => 'A',
				],
				[
					'text' => 'B',
				]
			],
		]);

		$this->assertSame([
			'a' => $input
		], $fields->toFormValues());

		$fields->reset();

		$this->assertSame([
			'a' => []
		], $fields->toFormValues());
	}

	public function testResetWithPassthroughValues(): void
	{
		$fields = new Fields([
			'a' => [
				'type' => 'text',
			],
		], $this->model);

		$fields->passthrough(['b' => 'B'])->fill(['a' => 'A']);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toFormValues());

		$fields->reset();

		$this->assertSame([
			'a' => ''
		], $fields->toFormValues());
	}

	public function testSubmit(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A',
				],
				'b' => [
					'type'  => 'text',
					'value' => 'B',
				],
			],
			model: $this->model
		);

		$fields->submit([
			'a' => 'A updated',
		]);

		$this->assertSame([
			'a' => 'A updated',
			'b' => 'B',
		], $fields->toStoredValues());
	}

	public function testSubmitWithForceAndANoValueField(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A',
				],
				'b' => [
					'type'  => 'info',
					'value' => 'B',
				],
			],
			model: $this->model
		);

		$fields->submit(
			input: [
				'a' => 'A updated',
				'b' => 'B updated',
			],
			force: true
		);

		$this->assertSame([
			'a' => 'A updated',
		], $fields->toStoredValues(), 'The info field can never be submitted. It has no value.');
	}

	public function testSubmitWithForceAndAComplexDisabledField(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A',
				],
				'b' => [
					'type'     => 'date',
					'disabled' => true,
					'value'    => '2025-01-01',
				],
			],
			model: $this->model
		);

		$fields->submit(
			input: [
				'a' => 'A updated',
				'b' => '03.04.2025',
			],
			force: true
		);

		$this->assertSame([
			'a' => 'A updated',
			'b' => '2025-04-03',
		], $fields->toStoredValues(), 'The date field should still be able to format the value correctly even if it was disabled.');
	}

	public function testSubmitWithClosureValues(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A',
				],
			],
			model: $this->model
		);

		$fields->submit([
			'a' => fn ($value) => $value . ' updated'
		]);

		$this->assertSame([
			'a' => 'A updated'
		], $fields->toStoredValues());
	}

	public function testSubmitWithDisabledField(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A',
				],
				'b' => [
					'type'     => 'text',
					'disabled' => true,
					'value'    => 'B',
				],
			],
			model: $this->model
		);

		$fields->submit([
			'a' => 'A updated',
			'b' => 'B updated',
		]);

		$this->assertSame([
			'a' => 'A updated',
			'b' => 'B updated'
		], $fields->toStoredValues());
	}

	public function testSubmitWithPassthrough(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type' => 'text',
				],
			],
			model: $this->model
		);

		$input = [
			'a' => 'A',
			'b' => 'B',
		];

		$fields->submit($input, passthrough: false);

		$this->assertSame([
			'a' => 'A',
		], $fields->toStoredValues(), 'Unknown fields are not included');

		$fields->submit($input, passthrough: true);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toStoredValues(), 'Unknown fields are included');
	}

	public function testToArray(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
			],
			'b' => [
				'type'  => 'text',
			],
		], $this->model);

		$this->assertSame(['a' => 'a', 'b' => 'b'], $fields->toArray(fn ($field) => $field->name()));
	}

	public function testToFormValues(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'Value a'
			],
			'b' => [
				'type'  => 'text',
				'value' => 'Value b'
			],
		], $this->model);

		$this->assertSame(['a' => 'Value a', 'b' => 'Value b'], $fields->toFormValues());
	}

	public function testToFormValuesWithNonValueField(): void
	{
		$fields = new Fields([
			'a' => [
				'type' => 'info',
			],
			'b' => [
				'type' => 'text',
			],
		], $this->model);

		$fields->fill([
			'a' => 'Value a',
			'b' => 'Value b',
		]);

		$this->assertSame([
			'b' => 'Value b',
		], $fields->toFormValues());
	}

	public function testToProps(): void
	{
		$this->setUpSingleLanguage();
		$this->app->impersonate('kirby');

		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A'
				]
			],
			model: $this->model
		);

		$this->assertSame([
			'a' => [
				'autofocus'  => false,
				'counter'    => true,
				'default'    => '',
				'disabled'   => false,
				'font'       => 'sans-serif',
				'hidden'     => false,
				'label'      => 'A',
				'name'       => 'a',
				'required'   => false,
				'saveable'   => true,
				'translate'  => true,
				'type'       => 'text',
				'width'      => '1/1',
			],
		], $fields->toProps());
	}

	public static function modelProvider(): array
	{
		return [
			[new Page(['slug' => 'test']), true],
			[new Site(), true],
			[new User(['email' => 'test@getkirby.com']), false],
			[new File(['filename' => 'test.jpg', 'parent' => new Site()]), false],
		];
	}

	public function testToPropsWithoutUpdatePermission(): void
	{
		$this->setUpSingleLanguage();

		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A'
				]
			],
			model: $this->model
		);

		$this->assertTrue($fields->toProps()['a']['disabled']);
	}

	public function testToPropsForNonTranslatableField(): void
	{
		$this->setUpMultiLanguage();
		$this->app->impersonate('kirby');

		$fields = new Fields(
			fields: [
				'a' => [
					'translate' => false,
					'type'      => 'text',
					'value'     => 'A',
				],
				'b' => [
					'type'      => 'text',
					'value'     => 'B',
				]
			],
			model: $this->model,
			language: $this->app->language('de')
		);

		$props = $fields->toProps();

		$this->assertTrue($props['a']['disabled']);
		$this->assertFalse($props['a']['translate']);

		$this->assertFalse($props['b']['disabled']);
		$this->assertTrue($props['b']['translate']);
	}

	public function testToStoredValues(): void
	{
		Field::$types['test'] = [
			'save' => function ($value) {
				return $value . ' stored';
			}
		];

		$fields = new Fields([
			'a' => [
				'type'  => 'test',
				'value' => 'Value a'
			],
			'b' => [
				'type'  => 'test',
				'value' => 'Value b'
			],
		], $this->model);

		$this->assertSame(['a' => 'Value a', 'b' => 'Value b'], $fields->toFormValues());
		$this->assertSame(['a' => 'Value a stored', 'b' => 'Value b stored'], $fields->toStoredValues());
	}

	public function testToStoredValuesWithNonValueField(): void
	{
		$fields = new Fields([
			'a' => [
				'type' => 'info',
			],
			'b' => [
				'type' => 'text',
			],
		], $this->model);

		$fields->fill([
			'a' => 'Value a',
			'b' => 'Value b',
		]);

		$this->assertSame([
			'b' => 'Value b',
		], $fields->toStoredValues());
	}

	public function testValidate(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'     => 'text',
					'required' => true,
				]
			],
			model: $this->model
		);

		$this->expectException(FormValidationException::class);
		$this->expectExceptionMessage('Form validation failed');

		$fields->validate();
	}

	public function testValidateWithoutErrors(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type' => 'text',
				]
			],
			model: $this->model
		);

		$this->assertNull($fields->validate());
	}
}
