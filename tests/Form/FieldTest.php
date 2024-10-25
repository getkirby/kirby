<?php

namespace Kirby\Form;

use Kirby\Cms\ModelWithContent;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;

/**
 * @coversDefaultClass \Kirby\Form\Field
 */
class FieldTest extends FieldTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.Field';

	protected array $originalMixins;

	public function setUp(): void
	{
		parent::setUp();

		Field::$types = [];

		// make a backup of the system mixins
		$this->originalMixins = Field::$mixins;
	}

	public function tearDown(): void
	{
		parent::tearDown();

		Field::$types  = [];
		Field::$mixins = $this->originalMixins;
	}

	protected function field(
		array $props = [],
		ModelWithContent|null $model = null
	): Field|FieldClass {
		// create the field type for the test
		Field::$types = [
			'test' => []
		];

		return new Field('test', [
			'model' => $model ?? $this->model,
			...$props
		]);
	}

	protected function fieldWithApiRoutes(): Field|FieldClass
	{
		$routes = $this->apiRoutes();

		Field::$types = [
			'test' => [
				'api' => fn () => $routes
			]
		];

		return new Field('test', [
			'model' => $this->model
		]);
	}

	protected function fieldWithComputedValue(): Field|FieldClass
	{
		Field::$types = [
			'test' => [
				'computed' => [
					'computedValue' => fn () => $this->value . ' computed'
				]
			]
		];

		return new Field('test', [
			'model' => $this->model
		]);
	}

	protected function fieldWithCustomStoreHandler(): Field|FieldClass
	{
		Field::$types = [
			'test' => [
				'save' => function (array $value = []): string {
					return implode(',', $value);
				}
			]
		];

		return new Field('test', [
			'model' => $this->model
		]);
	}

	protected function fieldWithDefaultIcon(): Field|FieldClass
	{
		Field::$types = [
			'test' => [
				'props' => [
					'icon' => fn (string $icon = 'test') => $icon
				]
			]
		];

		return new Field('test', [
			'model' => $this->model
		]);
	}

	protected function fieldWithDialogs(): Field|FieldClass
	{
		$routes = $this->dialogRoutes();

		Field::$types = [
			'test' => [
				'dialogs' => fn () => $routes
			]
		];

		return new Field('test', [
			'model' => $this->model
		]);
	}

	protected function fieldWithDrawers(): Field|FieldClass
	{
		$routes = $this->drawerRoutes();

		Field::$types = [
			'test' => [
				'drawers' => fn () => $routes
			]
		];

		return new Field('test', [
			'model' => $this->model
		]);
	}

	protected function fieldWithHiddenFlag(): Field|FieldClass
	{
		Field::$types = [
			'test' => [
				'hidden' => true
			]
		];

		return new Field('test', [
			'model' => $this->model
		]);
	}

	protected function fieldWithUnsaveableFlag(): Field|FieldClass
	{
		Field::$types = [
			'test' => [
				'save' => false
			]
		];

		return new Field('test', [
			'model' => $this->model
		]);
	}

	protected function fieldWithValidations(): Field|FieldClass
	{
		Field::$types = [
			'test' => [
				'validations' => [
					'maxlength',
					'custom' => function ($value) {
						if ($value !== null && $value !== 'a') {
							throw new Exception('Please enter an a');
						}
					}
				]
			]
		];

		return new Field('test', [
			'model'     => $this->model,
			'maxlength' => 5
		]);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructInvalidType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Field "foo": The field type "test" does not exist');

		new Field('test', [
			'name' => 'foo',
			'type' => 'foo'
		]);
	}

	public function testMixinMin()
	{
		Field::$mixins['min'] = include kirby()->root('kirby') . '/config/fields/mixins/min.php';

		Field::$types = [
			'test' => ['mixins' => ['min']]
		];

		$field = new Field('test', [
			'model' => $this->model,
		]);

		$this->assertFalse($field->isRequired());
		$this->assertNull($field->min());

		$field = new Field('test', [
			'model' => $this->model,
			'min'   => 5
		]);

		$this->assertTrue($field->isRequired());
		$this->assertSame(5, $field->min());

		$field = new Field('test', [
			'model' => $this->model,
			'required' => true
		]);

		$this->assertTrue($field->isRequired());
		$this->assertSame(1, $field->min());

		$field = new Field('test', [
			'model'    => $this->model,
			'required' => true,
			'min'      => 5
		]);

		$this->assertTrue($field->isRequired());
		$this->assertSame(5, $field->min());
	}
}
