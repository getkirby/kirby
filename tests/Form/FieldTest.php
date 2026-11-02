<?php

namespace Kirby\Form;

use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field\HiddenField;
use Kirby\Form\Field\InfoField;
use Kirby\Form\Field\TestCase;
use Kirby\Reflection\Attributes\Prop;
use PHPUnit\Framework\Attributes\CoversClass;

class MockField extends Field
{
}

class OptionsMockField extends Field
{
	protected array|null $cache = null;

	#[Prop]
	protected bool $enabled = true;
	protected Page|null $page = null;

	#[Prop]
	protected array $settings = ['size' => 'small', 'colors' => ['red', 'blue']];

	#[Prop]
	protected array $tags = ['a', 'b', 'c'];

	public function config(): array
	{
		return ['enabled' => false];
	}

	public function option(string $name): mixed
	{
		return $this->$name;
	}
}

class ConstructorMockField extends Field
{
	#[Prop]
	protected string $peter = 'pan';

	public function __construct(
		protected string $homer = 'simpson'
	) {
		parent::__construct();
	}

	public function option(string $name): mixed
	{
		return $this->$name;
	}
}

class VariadicConstructorMockField extends Field
{
	#[Prop]
	protected string $peter = 'pan';

	public function __construct(
		protected string $homer = 'simpson',
		mixed ...$args
	) {
		parent::__construct(...$args);
	}

	public function option(string $name): mixed
	{
		return $this->$name;
	}
}

class RedeclaredMockField extends OptionsMockField
{
	// redeclared without attribute to change the default
	protected bool $enabled = false;
}

class FieldWithDialogsField extends Field
{
}

class BrokenField extends Field
{
	public function props(): array
	{
		throw new InvalidArgumentException(message: 'Broken props');
	}
}

#[CoversClass(Field::class)]
class FieldTest extends TestCase
{
	public function test__construct(): void
	{
		$field = new MockField(name: 'test');
		$this->assertSame('test', $field->name());

		// siblings
		$siblings = new Fields([
			new MockField(name: 'a'),
			new MockField(name: 'b')
		]);
		$field = new MockField(siblings: $siblings);
		$this->assertCount(2, $field->siblings());

		// model
		$model = new Page(['slug' => 'test']);
		$field = new MockField(model: $model);
		$this->assertSame($model, $field->model());

		// unknown props are ignored
		$field = new MockField(name: 'test', foo: 'bar');
		$this->assertSame('test', $field->name());
	}

	public function test__constructWithCustomConstructor(): void
	{
		$model = new Page(['slug' => 'test']);
		$field = new VariadicConstructorMockField(
			homer: 'jay',
			model: $model,
			name: 'test',
			peter: 'shaw'
		);

		$this->assertSame('test', $field->name());
		$this->assertSame($model, $field->model());
		$this->assertSame('jay', $field->option('homer'));
		$this->assertSame('shaw', $field->option('peter'));
	}

	public function test__constructWithModelInConfig(): void
	{
		// the model is available before the config is read
		$model = new Page(['slug' => 'test']);
		$field = new class (model: $model) extends MockField {
			public function config(): array
			{
				return ['name' => $this->model()->slug()];
			}
		};

		$this->assertSame('test', $field->name());
	}

	public function test__constructWithValue(): void
	{
		$field = new HiddenField(value: 'test');
		$this->assertSame('test', $field->toFormValue());

		// the value is filled after all other props are set
		$field = new class (name: 'hidden', value: 'test') extends HiddenField {
			public function fill(mixed $value): static
			{
				return parent::fill($this->name() . ':' . $value);
			}
		};
		$this->assertSame('hidden:test', $field->toFormValue());

		// fields without a value ignore it
		$field = new MockField(value: 'test');
		$this->assertFalse($field->hasValue());
	}

	public function test__toString(): void
	{
		$field = new MockField(name: 'my-field');
		$this->assertSame('my-field', (string)$field);
	}

	public function testConfig(): void
	{
		$field = new OptionsMockField();
		$this->assertFalse($field->option('enabled'));

		// props win over config
		$field = new OptionsMockField(enabled: true);
		$this->assertTrue($field->option('enabled'));
	}

	public function testDialogs(): void
	{
		$field = new MockField();
		$this->assertSame([], $field->dialogs());
	}

	public function testDrawers(): void
	{
		$field = new MockField();
		$this->assertSame([], $field->drawers());
	}

	public function testError(): void
	{
		$field = Field::error('test', 'Something went wrong');

		$this->assertInstanceOf(InfoField::class, $field);
		$this->assertSame('test', $field->name());
		$this->assertSame('Error', $field->label());
		$this->assertSame('negative', $field->theme());
		$this->assertSame('<p>Something went wrong</p>', (string)$field->text());
	}

	public function testErrors(): void
	{
		// fields without a value cannot have errors
		$field = new MockField();
		$this->assertSame([], $field->errors());
	}

	public function testErrorsWithValidation(): void
	{
		// `Kirby\Form\Mixin\Validation` overwrites the default
		// for all fields that can hold a value
		$field = $this->field('text', [
			'name'     => 'test',
			'required' => true
		]);

		$this->assertSame([
			'required' => 'Please enter something'
		], $field->errors());
	}

	public function testHasValue(): void
	{
		// only fields that extend `ValueField` hold a value,
		// declaring a `$value` property is not enough
		$field = new MockField();
		$this->assertFalse($field->hasValue());

		$field = new class () extends MockField {
			protected string|null $value = null;
		};
		$this->assertFalse($field->hasValue());

		$field = new HiddenField();
		$this->assertTrue($field->hasValue());
	}

	public function testIsActive(): void
	{
		$fields = new Fields([
			'a' => ['type' => 'text', 'value' => 'b'],
			'b' => ['type' => 'text', 'when' => ['a' => 'b']],
		]);

		$this->assertTrue($fields->get('b')->isActive());
	}

	public function testIsActiveWithFieldWithoutValue(): void
	{
		$fields = new Fields([
			'a' => ['type' => 'info'],
			'b' => ['type' => 'text', 'when' => ['a' => true]],
		]);

		$this->assertFalse($fields->get('b')->isActive());
	}

	public function testIsActiveWithMissingField(): void
	{
		$fields = new Fields([
			'b' => ['type' => 'text', 'when' => ['a' => true]],
		]);

		$this->assertFalse($fields->get('b')->isActive());
	}

	public function testisHidden(): void
	{
		$field = new MockField();
		$this->assertFalse($field->isHidden());
	}

	public function testIsSubmittable(): void
	{
		// fields without a value are never submitted
		$field = new MockField();
		$this->assertFalse($field->isSubmittable(Language::ensure()));

		// `Kirby\Form\Mixin\Value` overwrites the default
		$field = $this->field('text', ['name' => 'test']);
		$this->assertTrue($field->isSubmittable(Language::ensure()));
	}

	public function testLabel(): void
	{
		// default label is null; subclasses (via Mixin\Label) override
		$field = new MockField();
		$this->assertNull($field->label());
	}

	public function testName(): void
	{
		$field = new MockField(name: 'test');
		$this->assertSame('test', $field->name());
		$this->assertSame('test', $field->id());

		// fallback to type
		$field = new MockField();
		$this->assertSame('mock', $field->name());
	}

	public function testProps(): void
	{
		$field = new MockField();
		$props = $field->props();

		ksort($props);

		$expected = [
			'hidden'      => false,
			'name'        => 'mock',
			'saveable'    => false,
			'type'        => 'mock',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testRegisterWithArrays(): void
	{
		$field = new OptionsMockField(
			settings: ['colors' => ['green']],
			tags: ['b']
		);

		// associative arrays are merged, lists are replaced
		$this->assertSame(['size' => 'small', 'colors' => ['green']], $field->option('settings'));
		$this->assertSame(['b'], $field->option('tags'));
	}

	public function testRegisterWithInvalidType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Field "test": The option "enabled" must be of type bool');

		new OptionsMockField(name: 'test', enabled: ['yes']);
	}

	public function testRegisterWithNull(): void
	{
		$field = new OptionsMockField(
			settings: null,
			width: null
		);

		$this->assertSame('1/1', $field->width());
		$this->assertSame(['size' => 'small', 'colors' => ['red', 'blue']], $field->option('settings'));
	}

	public function testRegisterWithPropertiesWithoutPropAttribute(): void
	{
		$field = new OptionsMockField(
			cache: ['foo'],
			page: 'test',
			value: 'test'
		);

		$this->assertNull($field->option('cache'));
		$this->assertNull($field->option('page'));
	}

	public function testRegisterWithRedeclaredProperty(): void
	{
		$field = new RedeclaredMockField(enabled: true);

		// the prop stays settable and exportable
		$this->assertTrue($field->option('enabled'));
		$this->assertTrue($field->props()['enabled']);
	}

	public function testRegisterWithUnknownProperty(): void
	{
		$field = new OptionsMockField(foo: 'bar');
		$this->assertFalse(isset($field->foo));
	}

	public function testPropsWithPropAttributes(): void
	{
		$props = (new OptionsMockField(name: 'test'))->props();

		// all `#[Prop]` properties are exported automatically
		$this->assertFalse($props['enabled']);
		$this->assertSame(['size' => 'small', 'colors' => ['red', 'blue']], $props['settings']);
		$this->assertSame(['a', 'b', 'c'], $props['tags']);

		// properties without the attribute are not exported
		$this->assertArrayNotHasKey('cache', $props);
		$this->assertArrayNotHasKey('page', $props);

		// props marked with `export: false` are not exported
		$this->assertArrayNotHasKey('default', $props);
		$this->assertArrayNotHasKey('translate', $props);
	}

	public function testResolve(): void
	{
		Field::$types['mock'] = MockField::class;

		$this->assertSame(MockField::class, Field::resolve('mock'));
	}

	public function testResolveWithArrayDefinition(): void
	{
		Field::$types['mock'] = ['props' => []];

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage(
			'The field type "mock" is registered as array. Array-based field ' .
			'definitions have been removed in Kirby 6. Please register the name ' .
			'of a class that extends Kirby\Form\Field instead.'
		);

		Field::resolve('mock');
	}

	public function testResolveWithForeignClass(): void
	{
		Field::$types['mock'] = Page::class;

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage(
			'The field type "mock" is registered as "Kirby\Cms\Page", ' .
			'which does not extend Kirby\Form\Field'
		);

		Field::resolve('mock');
	}

	public function testResolveWithNonVariadicConstructor(): void
	{
		Field::$types['mock'] = ConstructorMockField::class;

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage(
			'The constructor of "Kirby\Form\ConstructorMockField" must accept ' .
			'all other props as `mixed ...$args` and pass them on ' .
			'to `parent::__construct()`'
		);

		Field::resolve('mock');
	}

	public function testResolveWithMissingType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Field "test": The field type "mock" does not exist');

		Field::resolve('mock', 'test');
	}

	public function testStringTemplateWithEmptyValue(): void
	{
		$field = new class () extends MockField {
			public function stringTemplateTest($value)
			{
				return $this->stringTemplate($value);
			}
		};

		$this->assertNull($field->stringTemplateTest(null));
		$this->assertSame('', $field->stringTemplateTest(''));
	}

	public function testStringTemplateI18nWithEmptyValue(): void
	{
		$field = new class () extends MockField {
			public function stringTemplateI18nTest($value)
			{
				return $this->stringTemplateI18n($value);
			}
		};

		$this->assertNull($field->stringTemplateI18nTest(null));
		$this->assertSame('', $field->stringTemplateI18nTest(''));
	}

	public function testToArray(): void
	{
		$field = new MockField();
		$array = $field->toArray();

		ksort($array);

		$expected = [
			'hidden'      => false,
			'name'        => 'mock',
			'saveable'    => false,
			'type'        => 'mock',
			'width'       => '1/1',
		];

		$this->assertSame($expected, $array);
	}

	public function testToArrayWithBrokenProps(): void
	{
		// a field that cannot resolve its props is replaced
		// by an info field with the error message
		$field = new BrokenField(name: 'test');
		$array = $field->toArray();

		$this->assertSame('info', $array['type']);
		$this->assertSame('test', $array['name']);
		$this->assertSame('Error', $array['label']);
		$this->assertSame('negative', $array['theme']);
		$this->assertSame('<p>Broken props</p>', (string)$array['text']);
	}

	public function testType(): void
	{
		// a field created directly takes its type from the class name
		$field = new MockField();
		$this->assertSame('mock', $field->type());
	}

	public function testTypeFromConstructor(): void
	{
		// a field keeps the type it was created for,
		// even if its class name cannot express it
		$field = new MockField(type: 'my-mock');
		$this->assertSame('my-mock', $field->type());
		$this->assertSame('my-mock', $field->props()['type']);
	}

	public function testTypeWithFieldInClassName(): void
	{
		// only the suffix is removed from the class name
		$field = new FieldWithDialogsField();
		$this->assertSame('fieldwithdialogs', $field->type());
	}
}
