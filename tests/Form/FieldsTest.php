<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\TestCase;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Fields::class)]
class FieldsTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.Fields';

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

	public function testConstruct()
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
			],
			'b' => [
				'type'  => 'text',
			],
		], $this->model);

		$this->assertSame('a', $fields->first()->name());
		$this->assertSame($this->model, $fields->first()->model());
		$this->assertSame('b', $fields->last()->name());
		$this->assertSame($this->model, $fields->last()->model());
	}

	public function testConstructWithModel()
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
			],
			'b' => [
				'type'  => 'text',
			],
		], $this->model);

		$this->assertSame('a', $fields->first()->name());
		$this->assertSame($this->model, $fields->first()->model());
		$this->assertSame('b', $fields->last()->name());
		$this->assertSame($this->model, $fields->last()->model());
	}

	public function testDefaults()
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

	public function testErrors()
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

	public function testErrorsWithoutErrors()
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

	public function testFill()
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

		$fields->passthrough($input)->fill($input);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toFormValues(), 'Unknown fields are included');
	}

	public function testFind()
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

	public function testFindWhenFieldHasNoForm()
	{
		$fields = new Fields([
			'mother' => [
				'type'  => 'text',
			],
		], $this->model);

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

	public function testPassthroughWithExistingPassthroughValues(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'a'
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
			'a' => 'a',
			'c' => 'C'
		], $fields->toFormValues());
	}

	public function testPassthroughWithEmptyArray(): void
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'a'
			],
		], $this->model);

		// add passthrough values
		$fields->passthrough([
			'b' => 'B',
		]);

		// remove passthrough values
		$fields->passthrough();

		$this->assertSame([
			'a' => 'a',
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

		$fields->submit($input);

		$this->assertSame([
			'a' => 'A',
		], $fields->toStoredValues(), 'Unknown fields are not included');

		$fields->passthrough($input)->submit($input);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $fields->toStoredValues(), 'Unknown fields are included');
	}

	public function testToArray()
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

	public function testToFormValues()
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

	public static function modelProvider(): array
	{
		return [
			[new Page(['slug' => 'test']), true],
			[new Site(), true],
			[new User(['email' => 'test@getkirby.com']), false],
			[new File(['filename' => 'test.jpg', 'parent' => new Site()]), false],
		];
	}

	#[DataProvider('modelProvider')]
	public function testToPropsWithSkippedTitleFieldForPage(ModelWithContent $model, bool $skip): void
	{
		$this->setUpSingleLanguage();

		$fields = new Fields(
			fields: [
				'title' => [
					'type' => 'text',
				],
				'subtitle' => [
					'type' => 'text',
				]
			],
			model: $model
		);

		$props = $fields->toProps();

		if ($skip === false) {
			$this->assertCount(2, $props);
			$this->assertArrayHasKey('title', $props);
			$this->assertArrayHasKey('subtitle', $props);
		} else {
			$this->assertCount(1, $props);
			$this->assertArrayHasKey('subtitle', $props);
		}
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

	public function testToStoredValues()
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
