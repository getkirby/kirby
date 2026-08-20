<?php

namespace Kirby\Form;

use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field\HiddenField;
use Kirby\Form\Field\InfoField;
use Kirby\Form\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class MockField extends Field
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
	public function test__toString(): void
	{
		$field = new MockField(name: 'my-field');
		$this->assertSame('my-field', (string)$field);
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

	public function testFactory(): void
	{
		$field = MockField::factory(['name' => 'test']);
		$this->assertInstanceOf(MockField::class, $field);
		$this->assertSame('test', $field->name());

		// siblings
		$siblings = new Fields([
			new MockField(name: 'a'),
			new MockField(name: 'b')
		]);
		$field = MockField::factory([], $siblings);
		$this->assertCount(2, $field->siblings());

		// model
		$model = new Page(['slug' => 'test']);
		$field = MockField::factory(['model' => $model]);
		$this->assertSame($model, $field->model());
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
}
