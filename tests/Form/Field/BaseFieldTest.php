<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Page;
use Kirby\Form\Fields;
use PHPUnit\Framework\Attributes\CoversClass;

class MockBaseField extends BaseField
{
}

#[CoversClass(BaseField::class)]
class BaseFieldTest extends TestCase
{
	public function testDialogs(): void
	{
		$field = new MockBaseField();
		$this->assertSame([], $field->dialogs());
	}

	public function testDrawers(): void
	{
		$field = new MockBaseField();
		$this->assertSame([], $field->drawers());
	}

	public function testFactory(): void
	{
		$field = MockBaseField::factory(['name' => 'test']);
		$this->assertInstanceOf(MockBaseField::class, $field);
		$this->assertSame('test', $field->name());

		// siblings
		$siblings = new Fields([
			new MockBaseField(name: 'a'),
			new MockBaseField(name: 'b')
		]);
		$field = MockBaseField::factory([], $siblings);
		$this->assertCount(2, $field->siblings());

		// model
		$model = new Page(['slug' => 'test']);
		$field = MockBaseField::factory(['model' => $model]);
		$this->assertSame($model, $field->model());
	}

	public function testHasValue(): void
	{
		$field = new MockBaseField();
		$this->assertFalse($field->hasValue());

		$field = new class () extends MockBaseField {
			protected string|null $value;
		};
		$this->assertTrue($field->hasValue());
	}

	public function testisHidden(): void
	{
		$field = new MockBaseField();
		$this->assertFalse($field->isHidden());
	}

	public function testName(): void
	{
		$field = new MockBaseField(name: 'test');
		$this->assertSame('test', $field->name());
		$this->assertSame('test', $field->id());

		// fallback to type
		$field = new MockBaseField();
		$this->assertSame('mockbase', $field->name());
	}

	public function testProps(): void
	{
		$field = new MockBaseField();
		$props = $field->props();

		ksort($props);

		$expected = [
			'hidden'      => false,
			'name'        => 'mockbase',
			'saveable'    => false,
			'type'        => 'mockbase',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testStringTemplateWithEmptyValue(): void
	{
		$field = new class () extends MockBaseField {
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
		$field = new class () extends MockBaseField {
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
		$field = new MockBaseField();
		$array = $field->toArray();

		ksort($array);

		$expected = [
			'hidden'      => false,
			'name'        => 'mockbase',
			'saveable'    => false,
			'type'        => 'mockbase',
			'width'       => '1/1',
		];

		$this->assertSame($expected, $array);
	}
}
