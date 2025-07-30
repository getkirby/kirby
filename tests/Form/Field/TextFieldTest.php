<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Fields;
use PHPUnit\Framework\Attributes\DataProvider;

class TextFieldTest extends TestCase
{
	public function testConstructorWithAllParameters(): void
	{
		$page = new Page(['slug' => 'test']);
		$siblings = new Fields([], $page);

		$field = new TextField(
			after: 'after content',
			autofocus: true,
			before: 'before content',
			converter: 'lower',
			counter: false,
			default: 'default value',
			disabled: true,
			font: 'monospace',
			help: 'help text',
			icon: 'text',
			label: 'Text Label',
			maxlength: 100,
			minlength: 5,
			model: $page,
			name: 'test',
			pattern: '[a-z]+',
			placeholder: 'Enter text',
			required: true,
			siblings: $siblings,
			spellcheck: true,
			translate: false,
			when: ['field' => 'value'],
			width: '1/2',
			value: 'Test Value'
		);

		$this->assertSame('after content', $field->after());
		$this->assertTrue($field->autofocus());
		$this->assertSame('before content', $field->before());
		$this->assertSame('lower', $field->converter());
		$this->assertFalse($field->counter());
		$this->assertSame('default value', $field->default());
		$this->assertTrue($field->disabled());
		$this->assertSame('monospace', $field->font());
		$this->assertSame('<p>help text</p>', $field->help());
		$this->assertSame('text', $field->icon());
		$this->assertSame('Text Label', $field->label());
		$this->assertSame(100, $field->maxlength());
		$this->assertSame(5, $field->minlength());
		$this->assertSame($page, $field->model());
		$this->assertSame('test', $field->name());
		$this->assertSame('[a-z]+', $field->pattern());
		$this->assertSame('Enter text', $field->placeholder());
		$this->assertTrue($field->required());
		$this->assertSame($siblings, $field->siblings());
		$this->assertTrue($field->spellcheck());
		$this->assertFalse($field->translate());
		$this->assertSame(['field' => 'value'], $field->when());
		$this->assertSame('1/2', $field->width());
		$this->assertSame('test value', $field->value());
	}

	public function testConstructorWithMinimalParameters(): void
	{
		$field = new TextField();

		$this->assertNull($field->after());
		$this->assertFalse($field->autofocus());
		$this->assertNull($field->before());
		$this->assertNull($field->converter());
		$this->assertTrue($field->counter());
		$this->assertNull($field->default());
		$this->assertFalse($field->disabled());
		$this->assertSame('sans-serif', $field->font());
		$this->assertNull($field->help());
		$this->assertNull($field->icon());
		$this->assertSame('Text', $field->label());
		$this->assertNull($field->maxlength());
		$this->assertNull($field->minlength());
		$this->assertInstanceOf(Site::class, $field->model());
		$this->assertSame('text', $field->name());
		$this->assertNull($field->pattern());
		$this->assertNull($field->placeholder());
		$this->assertFalse($field->required());
		$this->assertFalse($field->spellcheck());
		$this->assertTrue($field->translate());
		$this->assertNull($field->when());
		$this->assertSame('1/1', $field->width());
	}

	public function testFactoryMethod(): void
	{
		$field = TextField::factory([
			'name'      => 'test',
			'value'     => 'test value',
			'converter' => 'upper'
		]);

		$this->assertInstanceOf(TextField::class, $field);
		$this->assertSame('test', $field->name());
		$this->assertSame('TEST VALUE', $field->value());
		$this->assertSame('upper', $field->converter());
	}

	public function testFactoryMethodWithEmptyArray(): void
	{
		$field = TextField::factory();

		$this->assertInstanceOf(TextField::class, $field);
		$this->assertSame('text', $field->name());
	}

	public function testPropsMethod(): void
	{
		$field = new TextField(
			converter: 'slug',
			counter: false,
			font: 'mono',
			label: 'Test Field',
			maxlength: 50,
			minlength: 10,
			name: 'test',
			pattern: '[a-z]+',
			spellcheck: true
		);

		$props = $field->props();

		$this->assertIsArray($props);
		$this->assertSame('slug', $props['converter']);
		$this->assertFalse($props['counter']);
		$this->assertSame('sans-serif', $props['font']);
		$this->assertSame(50, $props['maxlength']);
		$this->assertSame(10, $props['minlength']);
		$this->assertSame('[a-z]+', $props['pattern']);
		$this->assertTrue($props['spellcheck']);

		// test that parent props are included
		$this->assertSame('test', $props['name']);
		$this->assertSame('Test Field', $props['label']);
	}

	public function testDefaultMethod(): void
	{
		$field = new TextField(
			converter: 'lower',
			default: 'DEFAULT TEXT'
		);

		$this->assertSame('default text', $field->default());
	}

	public function testDefaultMethodWithoutConverter(): void
	{
		$field = new TextField(default: 'DEFAULT TEXT');

		$this->assertSame('DEFAULT TEXT', $field->default());
	}

	public function testDefaultMethodWithNullDefault(): void
	{
		$field = new TextField(converter: 'lower');

		$this->assertSame('', $field->default());
	}

	public function testToFormValue(): void
	{
		$field = new TextField(
			converter: 'lower',
			value: 'TEST VALUE'
		);

		$this->assertSame('test value', $field->toFormValue());
		$this->assertIsString($field->toFormValue());
	}

	public function testToFormValueWithoutConverter(): void
	{
		$field = new TextField(value: 'TEST VALUE');

		$this->assertSame('TEST VALUE', $field->toFormValue());
	}

	public function testToFormValueWithNullValue(): void
	{
		$field = new TextField(converter: 'upper');

		$this->assertSame('', $field->toFormValue());
	}

	public function testToFormValueWithNumericValue(): void
	{
		$field = new TextField(value: 123, converter: 'upper');

		$this->assertSame('123', $field->toFormValue());
		$this->assertIsString($field->toFormValue());
	}

	public function testValidationsMethod(): void
	{
		$field = new TextField();
		$validations = $field->validations();

		$this->assertIsArray($validations);
		$this->assertSame(['minlength', 'maxlength', 'pattern'], $validations);
	}

	public static function converterDataProvider(): array
	{
		return [
			['slug', 'Super Nice Text', 'super-nice-text'],
			['upper', 'hello world', 'HELLO WORLD'],
			['lower', 'HELLO WORLD', 'hello world'],
			['ucfirst', 'hello world', 'Hello world'],
			['upper', null, ''],
			['lower', '', ''],
			['slug', '  spaced text  ', 'spaced-text'],
			['upper', 123, '123'],
			['lower', true, '1'],
		];
	}

	#[DataProvider('converterDataProvider')]
	public function testConverter($converter, $input, $expected): void
	{
		$field = new TextField(
			converter: $converter,
			default: $input,
			value: $input
		);

		$this->assertSame($expected, $field->value());
		$this->assertSame($expected, $field->default());
		$this->assertSame($expected, $field->toFormValue());
	}

	public function testInvalidConverter(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid converter "does-not-exist"');

		new TextField(converter: 'does-not-exist');
	}

	public function testConverterWithArrayValue(): void
	{
		$field = new TextField(converter: 'upper');

		// test array conversion through convert method
		$result = $field->convert(['hello', 'world']);
		$this->assertSame(['HELLO', 'WORLD'], $result);
	}

	public function testMinlengthValidation(): void
	{
		$field = new TextField(
			minlength: 10,
			value: 'test'
		);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('minlength', $field->errors());
	}

	public function testMinlengthValidationPassing(): void
	{
		$field = new TextField(
			minlength: 10,
			value: 'test value that is long enough'
		);

		$this->assertTrue($field->isValid());
		$this->assertEmpty($field->errors());
	}

	public function testMaxlengthValidation(): void
	{
		$field = new TextField(
			maxlength: 10,
			value: 'this is a very long text that exceeds the limit'
		);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('maxlength', $field->errors());
	}

	public function testMaxlengthValidationPassing(): void
	{
		$field = new TextField(
			value: 'short',
			maxlength: 10
		);

		$this->assertTrue($field->isValid());
		$this->assertEmpty($field->errors());
	}

	public function testPatternValidation(): void
	{
		$field = new TextField(
			value: 'test123',
			pattern: '^[a-z]+$'
		);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('pattern', $field->errors());
	}

	public function testPatternValidationPassing(): void
	{
		$field = new TextField(
			value: 'testvalue',
			pattern: '^[a-z]+$'
		);

		$this->assertTrue($field->isValid());
		$this->assertEmpty($field->errors());
	}

	public function testMultipleValidationErrors(): void
	{
		$field = new TextField(
			minlength: 5,
			pattern: '^[0-9]+$',
			value: 'AB'
		);

		$this->assertFalse($field->isValid());
		$errors = $field->errors();
		$this->assertArrayHasKey('minlength', $errors);
		$this->assertArrayHasKey('pattern', $errors);
	}

	public function testCounterProperty(): void
	{
		$fieldTrue = new TextField(counter: true);
		$fieldFalse = new TextField(counter: false);

		$this->assertTrue($fieldTrue->counter());
		$this->assertFalse($fieldFalse->counter());
	}

	public function testFontProperty(): void
	{
		$field = new TextField();
		$this->assertSame('sans-serif', $field->font());

		$field = new TextField(font: 'monospace');
		$this->assertSame('monospace', $field->font());
	}

	public function testSpellcheckProperty(): void
	{
		$fieldTrue = new TextField(spellcheck: true);
		$fieldFalse = new TextField(spellcheck: false);

		$this->assertTrue($fieldTrue->spellcheck());
		$this->assertFalse($fieldFalse->spellcheck());
	}

	public function testPatternProperty(): void
	{
		$field = new TextField(pattern: '[0-9]+');
		$this->assertSame('[0-9]+', $field->pattern());

		$fieldNull = new TextField();
		$this->assertNull($fieldNull->pattern());
	}

	public function testMaxlengthProperty(): void
	{
		$field = new TextField(maxlength: 100);
		$this->assertSame(100, $field->maxlength());

		$fieldNull = new TextField();
		$this->assertNull($fieldNull->maxlength());
	}

	public function testMinlengthProperty(): void
	{
		$field = new TextField(minlength: 5);
		$this->assertSame(5, $field->minlength());

		$fieldNull = new TextField();
		$this->assertNull($fieldNull->minlength());
	}

	public function testConverterProperty(): void
	{
		$field = new TextField(converter: 'slug');
		$this->assertSame('slug', $field->converter());

		$fieldNull = new TextField();
		$this->assertNull($fieldNull->converter());
	}

	public function testFieldFactoryThroughTestCaseHelper(): void
	{
		$field = $this->field('text', [
			'name'  => 'test',
			'value' => 'test value'
		]);

		$this->assertInstanceOf(TextField::class, $field);
		$this->assertSame('test', $field->name());
		$this->assertSame('test value', $field->value());
	}

	public function testFieldWithModel(): void
	{
		$page = new Page(['slug' => 'test-page']);
		$field = new TextField(
			model: $page,
			name: 'title'
		);

		$this->assertSame($page, $field->model());
		$this->assertSame('title', $field->name());
	}

	public function testFieldWithSiblings(): void
	{
		$page = new Page(['slug' => 'test']);
		$siblings = new Fields([
			'title'       => ['type' => 'text'],
			'description' => ['type' => 'textarea']
		], $page);

		$field = new TextField(
			name: 'title',
			siblings: $siblings
		);

		$this->assertSame($siblings, $field->siblings());
	}

	public function testAllValidationsPassing(): void
	{
		$field = new TextField(
			maxlength: 20,
			minlength: 5,
			pattern: '^[a-z]+$',
			value: 'validtext'
		);

		$this->assertTrue($field->isValid());
		$this->assertEmpty($field->errors());
	}
}
