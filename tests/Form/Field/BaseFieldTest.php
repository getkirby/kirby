<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Fields;
use PHPUnit\Framework\Attributes\CoversClass;

class MockField extends BaseField
{
}

#[CoversClass(BaseField::class)]
class BaseFieldTest extends TestCase
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
		$field = new MockField();
		$this->assertFalse($field->hasValue());

		$field = new class () extends MockField {
			protected string|null $value;
		};
		$this->assertTrue($field->hasValue());
	}

	public function testisHidden(): void
	{
		$field = new MockField();
		$this->assertFalse($field->isHidden());
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
		BaseField::$types['mock'] = MockField::class;

		$this->assertSame(MockField::class, BaseField::resolve('mock'));
	}

	public function testResolveWithArrayDefinition(): void
	{
		BaseField::$types['mock'] = ['props' => []];

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage(
			'The field type "mock" is registered as array. Array-based field ' .
			'definitions have been removed in Kirby 6. Please register the name ' .
			'of a class that extends Kirby\Form\Field\BaseField instead.'
		);

		BaseField::resolve('mock');
	}

	public function testResolveWithForeignClass(): void
	{
		BaseField::$types['mock'] = Page::class;

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage(
			'The field type "mock" is registered as "Kirby\Cms\Page", ' .
			'which does not extend Kirby\Form\Field\BaseField'
		);

		BaseField::resolve('mock');
	}

	public function testResolveWithMissingType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Field "test": The field type "mock" does not exist');

		BaseField::resolve('mock', 'test');
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
}
