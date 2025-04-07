<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Form\Field
 */
class FieldTest extends TestCase
{
	protected array $originalMixins;

	public function setUp(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		Field::$types = [];

		// make a backup of the system mixins
		$this->originalMixins = Field::$mixins;
	}

	public function tearDown(): void
	{
		Field::$types = [];

		Field::$mixins = $this->originalMixins;
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

	public function testAfter()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'blog']);

		// untranslated
		$field = new Field('test', [
			'model' => $page,
			'after' => 'test'
		]);

		$this->assertSame('test', $field->after());
		$this->assertSame('test', $field->after);

		// translated
		$field = new Field('test', [
			'model' => $page,
			'after' => [
				'en' => 'en',
				'de' => 'de'
			]
		]);

		$this->assertSame('en', $field->after());
		$this->assertSame('en', $field->after);

		// with query
		$field = new Field('test', [
			'model' => $page,
			'after' => '{{ page.slug }}'
		]);

		$this->assertSame('blog', $field->after());
		$this->assertSame('blog', $field->after);
	}

	/**
	 * @covers ::api
	 * @covers ::routes
	 */
	public function testApi()
	{
		// no defined as default
		Field::$types = [
			'test' => []
		];

		$model = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $model,
		]);

		$this->assertSame([], $field->api());

		$routes = [
			[
				'pattern' => '/',
				'action'  => fn () => 'Hello World'
			]
		];

		// return simple string
		Field::$types = [
			'test' => [
				'api' => fn () => $routes
			]
		];

		$model = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $model,
		]);

		$this->assertSame($routes, $field->api());
	}

	public function testAutofocus()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		// default autofocus
		$field = new Field('test', [
			'model'  => $page,
		]);

		$this->assertFalse($field->autofocus());
		$this->assertFalse($field->autofocus);

		// enabled autofocus
		$field = new Field('test', [
			'model' => $page,
			'autofocus' => true
		]);

		$this->assertTrue($field->autofocus());
		$this->assertTrue($field->autofocus);
	}

	public function testBefore()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'blog']);

		// untranslated
		$field = new Field('test', [
			'model' => $page,
			'before' => 'test'
		]);

		$this->assertSame('test', $field->before());
		$this->assertSame('test', $field->before);

		// translated
		$field = new Field('test', [
			'model' => $page,
			'before' => [
				'en' => 'en',
				'de' => 'de'
			]
		]);

		$this->assertSame('en', $field->before());
		$this->assertSame('en', $field->before);

		// with query
		$field = new Field('test', [
			'model' => $page,
			'before' => '{{ page.slug }}'
		]);

		$this->assertSame('blog', $field->before());
		$this->assertSame('blog', $field->before);
	}

	public function testDefault()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'blog']);

		// default
		$field = new Field('test', [
			'model' => $page
		]);

		$this->assertNull($field->default());
		$this->assertNull($field->default);
		$this->assertNull($field->value());
		$this->assertNull($field->value);

		// specific default
		$field = new Field('test', [
			'model'   => $page,
			'default' => 'test'
		]);

		$this->assertSame('test', $field->default());
		$this->assertSame('test', $field->data(true));

		// don't overwrite existing values
		$field = new Field('test', [
			'model'   => $page,
			'default' => 'test',
			'value'   => 'something'
		]);

		$this->assertSame('test', $field->default());
		$this->assertSame('something', $field->value());
		$this->assertSame('something', $field->data(true));

		// with query
		$field = new Field('test', [
			'model' => $page,
			'default' => '{{ page.slug }}'
		]);

		$this->assertSame('blog', $field->default());
		$this->assertSame('blog', $field->data(true));
	}

	/**
	 * @covers ::dialogs
	 */
	public function testDialogs()
	{
		// no defined as default
		Field::$types = [
			'test' => []
		];

		$model = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $model,
		]);

		$this->assertSame([], $field->dialogs());

		// test dialogs
		$routes = [
			[
				'pattern' => 'foo',
				'load'    => function () {
				},
				'submit'  => function () {
				}
			]
		];

		// return routes
		Field::$types = [
			'test' => [
				'dialogs' => fn () => $routes
			]
		];

		$field = new Field('test', [
			'model' => $model,
		]);

		$this->assertSame($routes, $field->dialogs());
	}

	/**
	 * @covers ::drawers
	 */
	public function testDrawers()
	{
		// no defined as default
		Field::$types = [
			'test' => []
		];

		$model = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'model' => $model,
		]);

		$this->assertSame([], $field->drawers());

		// test drawers
		$routes = [
			[
				'pattern' => 'foo',
				'load'    => function () {
				},
				'submit'  => function () {
				}
			]
		];

		// return routes
		Field::$types = [
			'test' => [
				'drawers' => fn () => $routes
			]
		];

		$field = new Field('test', [
			'model' => $model,
		]);

		$this->assertSame($routes, $field->drawers());
	}

	/**
	 * @covers ::errors
	 */
	public function testErrors()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		// default
		$field = new Field('test', [
			'model' => $page,
		]);

		$this->assertSame([], $field->errors());

		// required
		$field = new Field('test', [
			'model'    => $page,
			'required' => true
		]);

		$expected = [
			'required' => 'Please enter something',
		];

		$this->assertSame($expected, $field->errors());
	}

	/**
	 * @covers ::fill
	 */
	public function testFill()
	{
		Field::$types = [
			'test' => [
				'computed' => [
					'computedValue' => fn () => $this->value . ' computed'
				]
			]
		];

		$page = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $page,
			'value' => 'test'
		]);

		$this->assertSame('test', $field->value());
		$this->assertSame('test computed', $field->computedValue());

		$field->fill('test2');

		$this->assertSame('test2', $field->value());
		$this->assertSame('test2 computed', $field->computedValue());
	}

	public function testFillWithRestoredState()
	{
		Field::$types = [
			'test' => $definition = [
				'computed' => [
					'options' => fn () => ['a', 'b', 'c']
				],
				'methods' => [
					'optionsDebugger' => fn () => $this->options
				]
			]
		];

		$page = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $page,
			'value' => 'test'
		]);

		$this->assertSame(['a', 'b', 'c'], $field->options());
		$this->assertEquals(Field::setup('test'), $field->optionsDebugger());

		// filling a new value must not break the mandatory
		// component definition properties
		$field->fill('test2');

		$this->assertSame(['a', 'b', 'c'], $field->options());
		$this->assertEquals(Field::setup('test'), $field->optionsDebugger());
	}

	public function testHelp()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		// untranslated
		$field = new Field('test', [
			'model'  => $page,
			'help' => 'test'
		]);

		$this->assertSame('<p>test</p>', $field->help());
		$this->assertSame('<p>test</p>', $field->help);

		// translated
		$field = new Field('test', [
			'model' => $page,
			'help' => [
				'en' => 'en',
				'de' => 'de'
			]
		]);

		$this->assertSame('<p>en</p>', $field->help());
		$this->assertSame('<p>en</p>', $field->help);
	}

	public function testIcon()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		// default
		$field = new Field('test', [
			'model' => $page,
		]);

		$this->assertNull($field->icon());
		$this->assertNull($field->icon);

		// specific icon
		$field = new Field('test', [
			'model' => $page,
			'icon'  => 'test'
		]);

		$this->assertSame('test', $field->icon());
		$this->assertSame('test', $field->icon);

		Field::$types = [
			'test' => [
				'props' => [
					'icon' => fn (string $icon = 'test') => $icon
				]
			]
		];

		// prop default
		$field = new Field('test', [
			'model' => $page,
		]);

		$this->assertSame('test', $field->icon());
		$this->assertSame('test', $field->icon);
	}

	public static function emptyValuesProvider(): array
	{
		return [
			['', true],
			[null, true],
			[[], true],
			[0, false],
			['0', false]
		];
	}

	/**
	 * @covers ::isDisabled
	 */
	public function testDisabled()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		// default state
		$field = new Field('test', [
			'model'  => $page
		]);

		$this->assertFalse($field->disabled());
		$this->assertFalse($field->disabled);
		$this->assertFalse($field->isDisabled());

		// disabled
		$field = new Field('test', [
			'model' => $page,
			'disabled' => true
		]);

		$this->assertTrue($field->disabled());
		$this->assertTrue($field->disabled);
		$this->assertTrue($field->isDisabled());
	}

	/**
	 * @covers ::isEmpty
	 * @covers ::isEmptyValue
	 * @dataProvider emptyValuesProvider
	 */
	public function testIsEmpty($value, $expected)
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $page,
			'value' => $value
		]);

		$this->assertSame($expected, $field->isEmpty());
		$this->assertSame($expected, $field->isEmptyValue($value));
	}

	/**
	 * @covers ::isEmptyValue
	 */
	public function testIsEmptyValueFromOption()
	{
		Field::$types = [
			'test' => [
				'isEmpty' => function ($value) {
					return $value === 'empty';
				}
			]
		];

		$page = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $page,
		]);

		$this->assertFalse($field->isEmptyValue('test'));
		$this->assertTrue($field->isEmptyValue('empty'));
	}

	/**
	 * @covers ::isHidden
	 */
	public function testIsHidden()
	{
		// default
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $page,
		]);

		$this->assertFalse($field->isHidden());

		// hidden
		Field::$types = [
			'test' => [
				'hidden' => true
			]
		];

		$field = new Field('test', [
			'model' => $page,
		]);

		$this->assertTrue($field->isHidden());
	}

	/**
	 * @covers ::isInvalid
	 * @covers ::isValid
	 */
	public function testIsInvalidOrValid()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		// default
		$field = new Field('test', [
			'model' => $page,
		]);

		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// required
		$field = new Field('test', [
			'model'    => $page,
			'required' => true
		]);

		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());
	}

	/**
	 * @covers ::isRequired
	 */
	public function testIsRequired()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $page,
		]);

		$this->assertFalse($field->isRequired());

		$field = new Field('test', [
			'model'    => $page,
			'required' => true
		]);

		$this->assertTrue($field->isRequired());
	}

	/**
	 * @covers ::isSaveable
	 * @covers ::save
	 */
	public function testIsSaveable()
	{
		Field::$types = [
			'store-me' => [
				'save' => true
			],
			'dont-store-me' => [
				'save' => false
			]
		];

		$page = new Page(['slug' => 'test']);

		$a = new Field('store-me', [
			'model' => $page
		]);

		$this->assertTrue($a->isSaveable());
		$this->assertTrue($a->save());

		$b = new Field('dont-store-me', [
			'model' => $page
		]);

		$this->assertFalse($b->isSaveable());
		$this->assertFalse($b->save());
	}

	/**
	 * @covers ::kirby
	 */
	public function testKirby()
	{
		Field::$types = [
			'test' => []
		];

		$field = new Field('test', [
			'model' => $model = new Page(['slug' => 'test'])
		]);

		$this->assertSame($model->kirby(), $field->kirby());
	}

	public function testLabel()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'blog']);

		// untranslated
		$field = new Field('test', [
			'model'  => $page,
			'label' => 'test'
		]);

		$this->assertSame('test', $field->label());
		$this->assertSame('test', $field->label);

		// translated
		$field = new Field('test', [
			'model' => $page,
			'label' => [
				'en' => 'en',
				'de' => 'de'
			]
		]);

		$this->assertSame('en', $field->label());
		$this->assertSame('en', $field->label);

		// with query
		$field = new Field('test', [
			'model' => $page,
			'label' => '{{ page.slug }}'
		]);

		$this->assertSame('blog', $field->label());
		$this->assertSame('blog', $field->label);
	}

	public function testMixinMin()
	{
		Field::$mixins['min'] = include kirby()->root('kirby') . '/config/fields/mixins/min.php';

		Field::$types = [
			'test' => ['mixins' => ['min']]
		];

		$page = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $page,
		]);

		$this->assertFalse($field->isRequired());
		$this->assertNull($field->min());

		$field = new Field('test', [
			'model' => $page,
			'min'   => 5
		]);

		$this->assertTrue($field->isRequired());
		$this->assertSame(5, $field->min());

		$field = new Field('test', [
			'model' => $page,
			'required' => true
		]);

		$this->assertTrue($field->isRequired());
		$this->assertSame(1, $field->min());

		$field = new Field('test', [
			'model'    => $page,
			'required' => true,
			'min'      => 5
		]);

		$this->assertTrue($field->isRequired());
		$this->assertSame(5, $field->min());
	}

	/**
	 * @covers ::model
	 */
	public function testModel()
	{
		Field::$types = [
			'test' => []
		];

		$field = new Field('test', [
			'model' => $model = new Page(['slug' => 'test'])
		]);

		$this->assertSame($model, $field->model());
	}

	public function testName()
	{
		Field::$types = [
			'test' => []
		];

		// no specific name. type should be used
		$field = new Field('test', [
			'model' => $model = new Page(['slug' => 'test'])
		]);

		$this->assertSame('test', $field->name());

		// specific name
		$field = new Field('test', [
			'model' => $model = new Page(['slug' => 'test']),
			'name'  => 'mytest'
		]);

		$this->assertSame('mytest', $field->name());
	}

	/**
	 * @covers ::needsValue
	 * @covers ::errors
	 */
	public function testNeedsValue()
	{
		$page = new Page(['slug' => 'test']);

		Field::$types = [
			'foo' => [],
			'bar' => [],
			'baz' => [],
		];

		$fields = new Fields([
			'foo' => [
				'type'  => 'foo',
				'model' => $page,
				'value' => 'a'
			],
			'bar' => [
				'type'  => 'bar',
				'model' => $page,
				'value' => 'b'
			],
			'baz' => [
				'type'  => 'baz',
				'model' => $page,
				'value' => 'c'
			]
		]);

		// default
		$field = new Field('foo', [
			'model' => $page,
		]);

		$this->assertSame([], $field->errors());

		// passed (simple)
		// 'bar' is required if 'foo' value is 'x'
		$field = new Field('bar', [
			'model' => $page,
			'required' => true,
			'when' => [
				'foo' => 'x'
			]
		], $fields);

		$this->assertSame([], $field->errors());

		// passed (multiple conditions without any match)
		// 'baz' is required if 'foo' value is 'x' and 'bar' value is 'y'
		$field = new Field('baz', [
			'model' => $page,
			'required' => true,
			'when' => [
				'foo' => 'x',
				'bar' => 'y'
			]
		], $fields);

		$this->assertSame([], $field->errors());

		// passed (multiple conditions with single match)
		// 'baz' is required if 'foo' value is 'a' and 'bar' value is 'y'
		$field = new Field('baz', [
			'model' => $page,
			'required' => true,
			'when' => [
				'foo' => 'a',
				'bar' => 'y'
			]
		], $fields);

		$this->assertSame([], $field->errors());

		// failed (simple)
		// 'bar' is required if 'foo' value is 'a'
		$field = new Field('bar', [
			'model' => $page,
			'required' => true,
			'when' => [
				'foo' => 'a'
			]
		], $fields);

		$expected = [
			'required' => 'Please enter something',
		];

		$this->assertSame($expected, $field->errors());

		// failed (multiple conditions)
		// 'baz' is required if 'foo' value is 'a' and 'bar' value is 'b'
		$field = new Field('baz', [
			'model' => $page,
			'required' => true,
			'when' => [
				'foo' => 'a',
				'bar' => 'b'
			]
		], $fields);

		$this->assertSame($expected, $field->errors());
	}

	public function testPlaceholder()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'blog']);

		// untranslated
		$field = new Field('test', [
			'model'       => $page,
			'placeholder' => 'test'
		]);

		$this->assertSame('test', $field->placeholder());
		$this->assertSame('test', $field->placeholder);

		// translated
		$field = new Field('test', [
			'model' => $page,
			'placeholder' => [
				'en' => 'en',
				'de' => 'de'
			]
		]);

		$this->assertSame('en', $field->placeholder());
		$this->assertSame('en', $field->placeholder);

		// with query
		$field = new Field('test', [
			'model' => $page,
			'placeholder' => '{{ page.slug }}'
		]);

		$this->assertSame('blog', $field->placeholder());
		$this->assertSame('blog', $field->placeholder);
	}

	/**
	 * @covers ::next
	 * @covers ::prev
	 * @covers ::siblingsCollection
	 */
	public function testPrevNext()
	{
		Field::$types = [
			'test' => []
		];

		$model = new Page(['slug' => 'test']);

		$siblings = new Fields([
			[
				'type' => 'test',
				'name' => 'a'
			],
			[
				'type' => 'test',
				'name' => 'b'
			]
		], $model);

		$this->assertNull($siblings->first()->prev());
		$this->assertNull($siblings->last()->next());
		$this->assertSame('b', $siblings->first()->next()->name());
		$this->assertSame('a', $siblings->last()->prev()->name());
	}

	/**
	 * @covers ::siblings
	 * @covers ::formFields
	 */
	public function testSiblings()
	{
		Field::$types = [
			'test' => []
		];

		$model = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $model,
		]);

		$this->assertInstanceOf(Fields::class, $field->siblings());
		$this->assertInstanceOf(Fields::class, $field->formFields());
		$this->assertCount(1, $field->siblings());
		$this->assertCount(1, $field->formFields());
		$this->assertSame($field, $field->siblings()->first());
		$this->assertSame($field, $field->formFields()->first());

		$field = new Field(
			type: 'test',
			attrs: [
				'model' => $model,
			],
			siblings: new Fields([
				new Field('test', [
					'model' => $model,
					'name'  => 'a'
				]),
				new Field('test', [
					'model' => $model,
					'name'  => 'b'
				]),
			])
		);

		$this->assertCount(2, $field->siblings());
		$this->assertCount(2, $field->formFields());
		$this->assertSame('a', $field->siblings()->first()->name());
		$this->assertSame('a', $field->formFields()->first()->name());
		$this->assertSame('b', $field->siblings()->last()->name());
		$this->assertSame('b', $field->formFields()->last()->name());
	}

	/**
	 * @covers ::toArray
	 */
	public function testToArray()
	{
		Field::$types = [
			'test' => [
				'props' => [
					'foo' => fn ($foo) => $foo
				]
			]
		];

		$field = new Field('test', [
			'model' => $model = new Page(['slug' => 'test']),
			'foo'   => 'bar'
		]);

		$array = $field->toArray();

		$this->assertSame('test', $array['name']);
		$this->assertSame('test', $array['type']);
		$this->assertSame('bar', $array['foo']);
		$this->assertSame('1/1', $array['width']);

		$this->assertArrayNotHasKey('model', $array);
	}

	/**
	 * @covers ::toFormValue
	 * @covers ::value
	 */
	public function testToFormValue()
	{
		Field::$types['test'] = [];

		$field = new Field('test');
		$this->assertNull($field->toFormValue());
		$this->assertNull($field->value());

		$field = new Field('test', ['value' => 'Test']);
		$this->assertSame('Test', $field->toFormValue());
		$this->assertSame('Test', $field->value());

		$field = new Field('test', ['default' => 'Default value']);
		$this->assertNull($field->toFormValue());
		$this->assertNull($field->value());

		$field = new Field('test', ['default' => 'Default value']);
		$this->assertSame('Default value', $field->toFormValue(true));
		$this->assertSame('Default value', $field->value(true));

		Field::$types['test'] = [
			'save' => false
		];

		$field = new Field('test', ['value' => 'Test']);
		$this->assertNull($field->toFormValue());
		$this->assertNull($field->value());
	}

	/**
	 * @covers ::toStoredValue
	 * @covers ::data
	 */
	public function testToStoredValue()
	{
		Field::$types = [
			'test' => [
				'props' => [
					'value' => fn ($value) => $value
				],
				'save' => fn ($value) => implode(', ', $value)
			]
		];

		$page = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $page,
			'value' => ['a', 'b', 'c']
		]);

		$this->assertSame('a, b, c', $field->toStoredValue());
		$this->assertSame('a, b, c', $field->data());
	}

	/**
	 * @covers ::toStoredValue
	 * @covers ::data
	 */
	public function testToStoredValueWhenUnsaveable()
	{
		Field::$types = [
			'test' => [
				'save' => false
			]
		];

		$model = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $model,
			'value' => 'something'
		]);

		$this->assertNull($field->toStoredValue());
		$this->assertNull($field->data());
	}

	/**
	 * @covers ::validate
	 * @covers ::validations
	 * @covers ::errors
	 */
	public function testValidate()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		// default
		$field = new Field('test', [
			'model'    => $page,
			'validate' => [
				'integer'
			],
		]);

		$this->assertSame([], $field->errors());

		// required
		$field = new Field('test', [
			'model'    => $page,
			'required' => true,
			'validate' => [
				'integer'
			],
		]);

		$expected = [
			'required' => 'Please enter something',
			'integer'  => 'Please enter a valid integer',
		];

		$this->assertSame($expected, $field->errors());

		// invalid
		$field = new Field('test', [
			'model'    => $page,
			'value'    => 'abc',
			'validate' => [
				'integer'
			],
		]);

		$expected = [
			'integer' => 'Please enter a valid integer',
		];

		$this->assertSame($expected, $field->errors());
	}

	/**
	 * @covers ::validate
	 * @covers ::validations
	 * @covers ::isValid
	 */
	public function testValidateByAttr()
	{
		Field::$types = [
			'test' => []
		];

		$model = new Page(['slug' => 'test']);

		// with simple string validation
		$field = new Field('test', [
			'model'    => $model,
			'value'    => 'https://getkirby.com',
			'validate' => 'url'
		]);
		$this->assertTrue($field->isValid());

		$field = new Field('test', [
			'model'    => $model,
			'value'    => 'definitely not a URL',
			'validate' => 'url'
		]);
		$this->assertFalse($field->isValid());

		// with an array of validators
		$field = new Field('test', [
			'model'    => $model,
			'value'    => 'thisIsATest',
			'validate' => [
				'startsWith' => 'this',
				'alpha'
			]
		]);
		$this->assertTrue($field->isValid());

		$field = new Field('test', [
			'model'    => $model,
			'value'    => 'thisIsATest',
			'validate' => [
				'startsWith' => 'that',
				'alpha'
			]
		]);
		$this->assertFalse($field->isValid());

		$field = new Field('test', [
			'model'    => $model,
			'value'    => 'thisIsA123',
			'validate' => [
				'startsWith' => 'this',
				'alpha'
			]
		]);
		$this->assertFalse($field->isValid());
	}

	/**
	 * @covers ::validate
	 * @covers ::validations
	 * @covers ::errors
	 * @covers ::isValid
	 */
	public function testValidateWithCustomValidator()
	{
		Field::$types = [
			'test' => [
				'validations' => [
					'test' => function ($value) {
						throw new InvalidArgumentException(
							message: 'Invalid value: ' . $value
						);
					}
				]
			]
		];

		$model = new Page(['slug' => 'test']);

		$field = new Field('test', [
			'model' => $model,
			'value' => 'abc'
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(['test' => 'Invalid value: abc'], $field->errors());
	}

	public function testWidth()
	{
		Field::$types = [
			'test' => []
		];

		$page = new Page(['slug' => 'test']);

		// default width
		$field = new Field('test', [
			'model' => $page,
		]);

		$this->assertSame('1/1', $field->width());
		$this->assertSame('1/1', $field->width);

		// specific width
		$field = new Field('test', [
			'model' => $page,
			'width' => '1/2'
		]);

		$this->assertSame('1/2', $field->width());
		$this->assertSame('1/2', $field->width);
	}
}
