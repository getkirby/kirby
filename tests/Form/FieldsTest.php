<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\TestCase;

/**
 * @coversDefaultClass \Kirby\Form\Fields
 */
class FieldsTest extends TestCase
{
	protected App $app;
	protected Page $model;

	public function setUp(): void
	{
		$this->app   = App::instance();
		$this->model = new Page(['slug' => 'test']);
	}

	/**
	 * @covers ::__construct
	 */
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

	/**
	 * @covers ::__construct
	 */
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

	/**
	 * @covers ::defaults
	 */
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

	/**
	 * @covers ::errors
	 */
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

	/**
	 * @covers ::errors
	 */
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

	/**
	 * @covers ::fill
	 */
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

	/**
	 * @covers ::findByKey
	 * @covers ::findByKeyRecursive
	 */
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

	/**
	 * @covers ::findByKey
	 * @covers ::findByKeyRecursive
	 */
	public function testFindWhenFieldHasNoForm()
	{
		$fields = new Fields([
			'mother' => [
				'type'  => 'text',
			],
		], $this->model);

		$this->assertNull($fields->find('mother+child'));
	}

	/**
	 * @covers ::toArray
	 */
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

	/**
	 * @covers ::toFormValues
	 */
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
}
