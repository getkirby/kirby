<?php

namespace Kirby\Form;

use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Fields::class)]
class FieldsTest extends TestCase
{
	protected Page $model;

	public function setUp(): void
	{
		parent::setUp();
		$this->model = new Page(['slug' => 'test']);
	}

	public function testConstruct(): void
	{
		$fields = new Fields([
			'a' => [
				'type' => 'text',
			],
			'b' => [
				'type' => 'text',
			],
		], $this->model);

		$this->assertSame('a', $fields->first()->name());
		$this->assertSame($this->model, $fields->first()->model());
		$this->assertSame('b', $fields->last()->name());
		$this->assertSame($this->model, $fields->last()->model());
	}

	public function testConstructWithModel(): void
	{
		$fields = new Fields([
			'a' => [
				'type' => 'text',
			],
			'b' => [
				'type' => 'text',
			],
		], $this->model);

		$this->assertSame('a', $fields->first()->name());
		$this->assertSame($this->model, $fields->first()->model());
		$this->assertSame('b', $fields->last()->name());
		$this->assertSame($this->model, $fields->last()->model());
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

		$this->assertSame(
			['a' => 'a', 'b' => 'b'],
			$fields->defaults()
		);
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
				'type' => 'text',
			],
			'b' => [
				'type' => 'text',
			],
		], $this->model);

		$this->assertSame([], $fields->errors());
	}

	public function testFill(): void
	{
		Field::$types['foo'] = [
			'save' => false
		];

		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'A'
			],
			'b' => [
				'type'  => 'text',
				'value' => 'B'
			],
			'c' => [
				'type'  => 'foo',
				'value' => 'C'
			],
		], $this->model);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B',
			'c' => null
		], $fields->toFormValues());

		$fields->fill([
			'a' => 'A updated',
			'b' => 'B updated',
			'c' => 'C updated',
			'd' => 'D new'
		]);

		$this->assertSame([
			'a' => 'A updated',
			'b' => 'B updated',
			'c' => null
		], $fields->toFormValues());
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

		$fields = new Fields([
			'mother' => [
				'type' => 'test',
			],
		], $this->model);

		$this->assertSame('mother', $fields->find('mother')->name());
		$this->assertSame('child', $fields->find('mother+child')->name());
		$this->assertNull($fields->find('mother+missing-child'));
	}

	public function testFindWhenFieldHasNoForm(): void
	{
		$fields = new Fields([
			'mother' => [
				'type' => 'text',
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
		$fields = new Fields([], language: $language);
		$this->assertSame('de', $fields->language()->code());
		$this->assertFalse($fields->language()->isDefault());
	}

	public function testSubmit(): void
	{
		Field::$types['foo'] = [
			'save' => false
		];

		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'value' => 'A'
			],
			'b' => [
				'type'  => 'text',
				'value' => 'B'
			],
			'c' => [
				'type'  => 'foo',
				'value' => 'C'
			],
			'd' => [
				'type'     => 'text',
				'value'    => 'D',
				'disabled' => true
			],
		], $this->model);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B',
			'd' => 'D'
		], $fields->toStoredValues());

		$fields->submit([
			'a' => 'A updated',
			'b' => 'B updated',
			'c' => 'C updated',
			'd' => 'D updated',
			'e' => 'E new'
		]);

		$this->assertSame([
			'a' => 'A updated',
			'b' => 'B updated',
			'd' => 'D'
		], $fields->toStoredValues());
	}

	public function testToArray(): void
	{
		$fields = new Fields([
			'a' => [
				'type' => 'text',
			],
			'b' => [
				'type' => 'text',
			],
		], $this->model);

		$this->assertSame(
			['a' => 'a', 'b' => 'b'],
			$fields->toArray(fn ($field) => $field->name())
		);
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

		$this->assertSame(
			['a' => 'Value a', 'b' => 'Value b'],
			$fields->toFormValues()
		);
	}

	public function testToStoredValues(): void
	{
		Field::$types['test'] = [
			'save' => fn ($value) => $value . ' stored'
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

		$this->assertSame(
			['a' => 'Value a', 'b' => 'Value b'],
			$fields->toFormValues()
		);
		$this->assertSame(
			['a' => 'Value a stored', 'b' => 'Value b stored'],
			$fields->toStoredValues()
		);
	}
}
