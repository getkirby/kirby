<?php

namespace Kirby\Form;

use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\TestCase;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field\UnknownField;
use PHPUnit\Framework\Attributes\CoversClass;

class UnfillableTestField extends FieldClass
{
	public function isFillable(): bool
	{
		return false;
	}
}

#[CoversClass(Fields::class)]
class FieldsTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.Fields';

	protected Page $model;

	public function setUp(): void
	{
		parent::setUp();
		$this->model = new Page(['slug' => 'test']);
	}

	public function testAppendUnknownFields(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type' => 'text',
				],
			],
			model: $this->model
		);

		$fields->appendUnknownFields([
			'a' => 'A',
			'b' => 'B',
		]);

		$this->assertCount(2, $fields);
		$this->assertInstanceOf(Field::class, $fields->get('a'));
		$this->assertInstanceOf(UnknownField::class, $fields->get('b'));
	}

	public function testConstruct(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				],
			],
			model: $this->model
		);

		$this->assertSame('a', $fields->first()->name());
		$this->assertSame($this->model, $fields->first()->model());
		$this->assertSame('b', $fields->last()->name());
		$this->assertSame($this->model, $fields->last()->model());
	}

	public function testConstructWithModel(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				],
			],
			model: $this->model
		);

		$this->assertSame('a', $fields->first()->name());
		$this->assertSame($this->model, $fields->first()->model());
		$this->assertSame('b', $fields->last()->name());
		$this->assertSame($this->model, $fields->last()->model());
	}

	public function testDefaults(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'default' => 'a',
					'type'    => 'text'
				],
				'b' => [
					'type'    => 'text'
				],
			],
			model: $this->model
		);

		$this->assertSame([
			'a' => 'a',
			'b' => null
		], $fields->defaults());
	}

	public function testErrors(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'label'    => 'A',
					'type'     => 'text',
					'required' => true
				],
				'b' => [
					'label'     => 'B',
					'type'      => 'text',
					'maxlength' => 3,
					'value'     => 'Too long'
				],
			],
			model: $this->model
		);

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
		$fields = new Fields(
			fields: [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				],
			],
			model: $this->model
		);

		$this->assertSame([], $fields->errors());
	}

	public function testFill(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A'
				],
				'b' => [
					'type'  => 'text',
					'value' => 'B'
				]
			],
			model: $this->model
		);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B',
		], $fields->toFormValues());

		$fields->fill([
			'a' => 'A updated'
		]);

		$this->assertSame([
			'a' => 'A updated',
			'b' => 'B',
		], $fields->toFormValues());
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

	public function testFillWithUnfillableField(): void
	{
		$fields = new Fields(
			fields: [
				'a' => new UnfillableTestField(['name' => 'a']),
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

	public function testFillWithUnknownFields(): void
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

		$fields->fill($input);

		$this->assertSame([
			'a' => 'A',
		], $fields->toFormValues(), 'Unknown fields are not included');

		$fields->fill($input, strict: false);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toFormValues(), 'Unknown fields are included');
	}

	public function testFind(): void
	{
		Field::$types['test'] = [
			'methods' => [
				'form' => fn () => new Form([
					'fields' => [
						'child' => [
							'type'  => 'text',
						],
					],
					'model' => $this->model
				])
			]
		];

		$fields = new Fields(
			fields: [
				'mother' => [
					'type' => 'test',
				],
			],
			model: $this->model
		);

		$this->assertSame('mother', $fields->find('mother')->name());
		$this->assertSame('child', $fields->find('mother+child')->name());
		$this->assertNull($fields->find('mother+missing-child'));
	}

	public function testFindWhenFieldHasNoForm(): void
	{
		$fields = new Fields(
			fields: [
				'mother' => [
					'type' => 'text',
				],
			],
			model: $this->model
		);

		$this->assertNull($fields->find('mother+child'));
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
	}

	public function testRemoveUnknownFields(): void
	{
		$fields = new Fields(
			fields: [
				'a' => new Field('text', ['name' => 'a']),
				'b' => new UnknownField(name: 'b'),
			],
			model: $this->model
		);

		$this->assertCount(2, $fields);

		$this->assertInstanceOf(Field::class, $fields->get('a'));
		$this->assertInstanceOf(UnknownField::class, $fields->get('b'));

		$fields->removeUnknownFields();

		$this->assertCount(1, $fields);
		$this->assertInstanceOf(Field::class, $fields->get('a'));
		$this->assertNull($fields->get('b'));
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
			'b' => 'B'
		], $fields->toStoredValues());
	}

	public function testSubmitWithUnknownField(): void
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

		$fields->submit($input);

		$this->assertSame([
			'a' => 'A',
		], $fields->toStoredValues(), 'Unknown fields are not included');

		$fields->submit($input, strict: false);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toStoredValues(), 'Unknown fields are included');
	}

	public function testToArray(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type' => 'text',
				],
				'b' => [
					'type' => 'text',
				],
			],
			model: $this->model
		);

		$this->assertSame(
			['a' => 'a', 'b' => 'b'],
			$fields->toArray(fn ($field) => $field->name())
		);
	}

	public function testToFormValues(): void
	{
		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A'
				],
				'b' => [
					'type'  => 'text',
					'value' => 'B'
				],
			],
			model: $this->model
		);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toFormValues());
	}

	public function testToFormValuesWithNonSaveableField(): void
	{
		Field::$types['test'] = [
			'save' => fn ($value) => $value . ' stored'
		];

		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'test',
					'value' => 'A'
				],
				'b' => [
					'type'  => 'test',
					'value' => 'B'
				],
			],
			model: $this->model
		);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toFormValues());
	}

	public function testToProps(): void
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

		$this->assertSame([
			'a' => [
				'autofocus'  => false,
				'counter'    => true,
				'disabled'   => false,
				'font'       => 'sans-serif',
				'hidden'     => false,
				'name'       => 'a',
				'required'   => false,
				'saveable'   => true,
				'spellcheck' => false,
				'translate'  => true,
				'type'       => 'text',
				'width'      => '1/1',
			],
		], $fields->toProps());
	}

	public function testToPropsForNonTranslatableField(): void
	{
		$this->setUpMultiLanguage();

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
		$this->setUpSingleLanguage();

		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'text',
					'value' => 'A'
				],
				'b' => [
					'type'  => 'text',
					'value' => 'B'
				]
			],
			model: $this->model
		);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toStoredValues());
	}

	public function testToStoredValuesWithNonSaveableField(): void
	{
		Field::$types['test'] = [
			'save' => fn ($value) => $value . ' stored'
		];

		$fields = new Fields(
			fields: [
				'a' => [
					'type'  => 'test',
					'value' => 'A'
				],
				'b' => [
					'type'  => 'test',
					'value' => 'B'
				],
			],
			model: $this->model
		);

		$this->assertSame([
			'a' => 'A stored',
			'b' => 'B stored'
		], $fields->toStoredValues());
	}

	public function testToStoredValuesWithNonTranslatableFieldsInPrimaryLanguage(): void
	{
		$this->setUpMultiLanguage();

		$fields = new Fields(
			fields: [
				'a' => [
					'type'      => 'text',
					'translate' => true,
					'value'     => 'A'
				],
				'b' => [
					'type'      => 'text',
					'translate' => false,
					'value'     => 'B'
				]
			],
			model: $this->model,
			language: $this->app->language('en')
		);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toStoredValues());
	}

	public function testToStoredValuesWithNonTranslatableFieldsInSecondaryLanguage(): void
	{
		$this->setUpMultiLanguage();

		$fields = new Fields(
			fields: [
				'a' => [
					'type'      => 'text',
					'translate' => true,
					'value'     => 'A'
				],
				'b' => [
					'type'      => 'text',
					'translate' => false,
					'value'     => 'B'
				]
			],
			model: $this->model,
			language: $this->app->language('de')
		);

		$this->assertSame([
			'a' => 'A'
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

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid form with errors');

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
