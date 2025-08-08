<?php

namespace Kirby\Form;

use Error;
use Exception;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Form\Mixin\Minlength;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class TestField extends FieldClass
{
}

class HiddenField extends FieldClass
{
	public function hidden(): bool
	{
		return true;
	}
}

class NoValueField extends FieldClass
{
	public function hasValue(): bool
	{
		return false;
	}
}

class ValidatedField extends FieldClass
{
	use Minlength;

	public function __construct(
		int|null $minlength = null,
		...$props
	) {
		parent::__construct(
			...$props
		);

		$this->setMinlength($minlength);
	}

	public function validations(): array
	{
		return [
			'minlength',
			'custom' => function ($value) {
				if ($value !== 'a') {
					throw new Exception('Please enter an a');
				}
			}
		];
	}
}

#[CoversClass(FieldClass::class)]
class FieldClassTest extends TestCase
{
	public function test__call(): void
	{
		$field = new TestField(
			foo: 'bar'
		);

		$this->assertSame('bar', $field->foo());
	}

	public function testAfter(): void
	{
		$field = new TestField();
		$this->assertNull($field->after());

		$field = new TestField(after: 'Test');
		$this->assertSame('Test', $field->after());

		$field = new TestField(after: ['en' => 'Test']);
		$this->assertSame('Test', $field->after());
	}

	public function testApi(): void
	{
		$field = new TestField();
		$this->assertSame([], $field->api());
	}

	public function testAutofocus(): void
	{
		$field = new TestField();
		$this->assertFalse($field->autofocus());

		$field = new TestField(autofocus: true);
		$this->assertTrue($field->autofocus());
	}

	public function testBefore(): void
	{
		$field = new TestField();
		$this->assertNull($field->before());

		$field = new TestField(before: 'Test');
		$this->assertSame('Test', $field->before());

		$field = new TestField(before: ['en' => 'Test']);
		$this->assertSame('Test', $field->before());
	}

	public function testData(): void
	{
		$field = new TestField();
		$this->assertNull($field->data());

		// use default value
		$field = new TestField(default: 'default value');
		$this->assertSame('default value', $field->data(true));

		// don't use default value
		$field = new TestField(default: 'default value');
		$this->assertNull($field->data());

		// use existing value
		$field = new TestField(value: 'test');
		$this->assertSame('test', $field->data());
	}

	public function testDefault(): void
	{
		$field = new TestField();
		$this->assertNull($field->default());

		// simple default value
		$field = new TestField(default: 'Test');
		$this->assertSame('Test', $field->default());

		// default value from string template
		$field = new TestField(
			default: '{{ page.title }}',
			model: new Page([
				'slug'    => 'test',
				'content' => [
					'title' => 'Test title'
				]
			])
		);

		$this->assertSame('Test title', $field->default());
	}

	public function testDialogs(): void
	{
		$field = new TestField();
		$this->assertSame([], $field->dialogs());
	}

	public function testDisabled(): void
	{
		$field = new TestField();
		$this->assertFalse($field->disabled());
		$this->assertFalse($field->isDisabled());

		$field = new TestField(disabled: true);
		$this->assertTrue($field->disabled());
		$this->assertTrue($field->isDisabled());
	}

	public function testDrawers(): void
	{
		$field = new TestField();
		$this->assertSame([], $field->drawers());
	}

	public function testErrors(): void
	{
		$field = new TestField();
		$this->assertSame([], $field->errors());

		$field = new TestField(required: true);
		$this->assertSame(['required' => 'Please enter something'], $field->errors());

		$field = new ValidatedField(value: 'a');
		$this->assertSame([], $field->errors());

		$field = new ValidatedField(value: 'a', minlength: 4);
		$this->assertSame(['minlength' => 'Please enter a longer value. (min. 4 characters)'], $field->errors());

		$field = new ValidatedField(value: 'b');
		$this->assertSame(['custom' => 'Please enter an a'], $field->errors());
	}

	public function testFill(): void
	{
		$field = new TestField();
		$this->assertNull($field->value());
		$field->fill('Test value');
		$this->assertSame('Test value', $field->value());
	}

	public function testIsEmpty(): void
	{
		$field = new TestField();
		$this->assertTrue($field->isEmpty());

		$field = new TestField(value: 'Test');
		$this->assertFalse($field->isEmpty());
	}

	public function testIsEmptyValue(): void
	{
		$field = new TestField();

		$this->assertTrue($field->isEmptyValue());
		$this->assertTrue($field->isEmptyValue(''));
		$this->assertTrue($field->isEmptyValue(null));
		$this->assertTrue($field->isEmptyValue([]));

		$this->assertFalse($field->isEmptyValue(' '));
		$this->assertFalse($field->isEmptyValue(0));
		$this->assertFalse($field->isEmptyValue('0'));
	}

	public function testHidden(): void
	{
		$field = new TestField();
		$this->assertFalse($field->hidden());

		$field = new HiddenField();
		$this->assertTrue($field->hidden());
	}

	public function testIsTranslatable(): void
	{
		$language = Language::ensure('current');

		$field = new TestField();
		$this->assertTrue($field->isTranslatable($language));
	}

	public function testIsTranslatableWithNonDefaultLanguage(): void
	{
		$language = new Language([
			'code'    => 'de',
			'default' => false
		]);

		$field = new TestField(translate: true);
		$this->assertTrue($field->isTranslatable($language));

		$field = new TestField(translate: false);
		$this->assertFalse($field->isTranslatable($language));
	}

	public function testInvalid(): void
	{
		$field = new TestField();
		$this->assertFalse($field->isInvalid());

		$field = new TestField(required: true);
		$this->assertTrue($field->isInvalid());

		$field = new TestField(required: true, value: 'Test');
		$this->assertFalse($field->isInvalid());
	}

	public function testIsRequired(): void
	{
		$field = new TestField();
		$this->assertFalse($field->isRequired());
		$this->assertFalse($field->required());

		$field = new TestField(required: true);
		$this->assertTrue($field->isRequired());
		$this->assertTrue($field->required());
	}

	public function testIsStorable(): void
	{
		$language = Language::ensure('current');

		$field = new TestField();
		$this->assertTrue($field->isStorable($language));

		$field = new NoValueField();
		$this->assertFalse($field->isStorable($language));
	}

	public function testIsStorableWithDisabledField(): void
	{
		$language = Language::ensure('current');

		$field = new TestField(disabled: true);
		$this->assertTrue($field->isStorable($language), 'The value of a storable field must not be changed on submit, but can still be stored.');
	}

	public function testIsStorableWithNonDefaultLanguage(): void
	{
		$language = new Language([
			'code'    => 'de',
			'default' => false
		]);

		$field = new TestField(['translate' => true]);
		$this->assertTrue($field->isStorable($language));

		$field = new TestField(translate: false);
		$this->assertFalse($field->isStorable($language));
	}

	public function testIsSubmittable(): void
	{
		$language = Language::ensure('current');

		$field = new TestField();
		$this->assertTrue($field->isSubmittable($language));

		$field = new NoValueField();
		$this->assertFalse($field->isSubmittable($language));
	}

	public function testIsSubmittableWithDisabledField(): void
	{
		$language = Language::ensure('current');

		$field = new TestField(disabled: true);
		$this->assertFalse($field->isSubmittable($language));
	}

	public function testIsSubmittableWithNonDefaultLanguage(): void
	{
		$language = new Language([
			'code'    => 'de',
			'default' => false
		]);

		$field = new TestField(translate: true);
		$this->assertTrue($field->isSubmittable($language));

		$field = new TestField(translate: false);
		$this->assertFalse($field->isSubmittable($language));
	}

	public function testIsSubmittableWithWhenQueryAndMatchingValue(): void
	{
		$language = Language::ensure('current');

		$siblings = new Fields([
			new TestField(name: 'a', value: 'b'),
		]);

		$field = new TestField([
			'siblings' => $siblings,
			'when'     => [
				'a' => 'b'
			],
		]);

		$this->assertTrue($field->isSubmittable($language));
	}

	public function testIsSubmittableWithWhenQueryAndNonMatchingValue(): void
	{
		$language = Language::ensure('current');

		$siblings = new Fields([
			new TestField(name: 'a', value: 'something-else'),
		]);

		$field = new TestField(
			siblings: $siblings,
			when: [
				'a' => 'b'
			]
		);

		$this->assertTrue($field->isSubmittable($language));
	}

	public function testIsSubmittableWithWhenQueryAndTwoFields()
	{
		$language = Language::ensure('current');

		$fields = new Fields([
			new TestField(
				name: 'a',
				value: 'a',
				when: [
					'b' => 'b'
				]
			),
			new TestField(
				name: 'b',
				value: 'b',
				when: [
					'a' => 'a'
				]
			),
		]);

		$a = $fields->get('a');
		$b = $fields->get('b');

		$this->assertTrue($a->isSubmittable($language));
		$this->assertTrue($b->isSubmittable($language));

		$fields->submit([
			'a' => 'a submitted',
			'b' => 'b submitted'
		]);

		$this->assertSame([
			'a' => 'a submitted',
			'b' => 'b submitted'
		], $fields->toFormValues());
	}

	public function testHasValue(): void
	{
		$field = new TestField();
		$this->assertTrue($field->hasValue());

		$field = new NoValueField();
		$this->assertFalse($field->hasValue());
	}

	public function testHelp(): void
	{
		$field = new TestField();
		$this->assertNull($field->help());

		// regular help
		$field = new TestField(help: 'Test');
		$this->assertSame('<p>Test</p>', $field->help());

		// translated help
		$field = new TestField(help: ['en' => 'Test']);
		$this->assertSame('<p>Test</p>', $field->help());

		// help from string template
		$field = new TestField(
			model: new Page([
				'slug'    => 'test',
				'content' => [
					'title' => 'Test title'
				]
			]),
			help: 'A field for {{ page.title }}'
		);

		$this->assertSame('<p>A field for Test title</p>', $field->help());
	}

	public function testIcon(): void
	{
		$field = new TestField();
		$this->assertNull($field->icon());

		$field = new TestField(icon: 'Test');
		$this->assertSame('Test', $field->icon());
	}

	public function testId(): void
	{
		$field = new TestField();
		$this->assertSame('test', $field->id());

		$field = new TestField(name: 'test-id');
		$this->assertSame('test-id', $field->id());
	}

	public function testKirby(): void
	{
		$field = new TestField();
		$this->assertSame(kirby(), $field->kirby());
	}

	public function testLabel(): void
	{
		$field = new TestField();
		$this->assertSame('Test', $field->label());

		$field = new TestField(label: 'Test');
		$this->assertSame('Test', $field->label());

		$field = new TestField(label: ['en' => 'Test']);
		$this->assertSame('Test', $field->label());
	}

	public function testModel(): void
	{
		$field = new TestField();
		$site = site();
		$this->assertIsSite($site, $field->model());

		$page = new Page(['slug' => 'test']);
		$field = new TestField(model: $page);
		$this->assertIsPage($page, $field->model());
	}

	public function testName(): void
	{
		$field = new TestField();
		$this->assertSame('test', $field->name());

		$field = new TestField(name: 'test-name');
		$this->assertSame('test-name', $field->name());
	}

	public function testNameCase(): void
	{
		$field = new TestField(name: 'myTest');
		$this->assertSame('mytest', $field->name());
	}

	public function testPlaceholder(): void
	{
		$field = new TestField();
		$this->assertNull($field->placeholder());

		// regular placeholder
		$field = new TestField(placeholder: 'Test');
		$this->assertSame('Test', $field->placeholder());

		// translated placeholder
		$field = new TestField(placeholder: ['en' => 'Test']);
		$this->assertSame('Test', $field->placeholder());

		// placeholder from string template
		$field = new TestField(
			model: new Page([
				'slug'    => 'test',
				'content' => [
					'title' => 'Test title'
				]
			]),
			placeholder: 'Placeholder for {{ page.title }}'
		);

		$this->assertSame('Placeholder for Test title', $field->placeholder());
	}

	public function testProps(): void
	{
		$props = [
			'after'       => 'After value',
			'autofocus'   => true,
			'before'      => 'Before value',
			'default'     => 'Default value',
			'disabled'    => false,
			'help'        => 'Help value',
			'hidden'      => false,
			'icon'        => 'Icon value',
			'label'       => 'Label value',
			'name'        => 'name-value',
			'placeholder' => 'Placeholder value',
			'required'    => true,
			'translate'   => false,
			'when'        => ['a' => 'b'],
			'width'       => '1/2'
		];

		$expected = [
			...$props,
			'help'     => '<p>Help value</p>',
			'hidden'   => false,
			'saveable' => true,
			'type'     => 'test'
		];

		$field = new TestField(...$props);
		$array = $field->toArray();

		$this->assertEquals($expected, $field->props());
	}

	public function testRoutes(): void
	{
		$field = new TestField();
		$this->assertSame([], $field->routes());
	}

	public function testSave(): void
	{
		$field = new TestField();
		$this->assertTrue($field->save());
	}

	public function testSiblings(): void
	{
		$field = new TestField();
		$this->assertInstanceOf(Fields::class, $field->siblings());
		$this->assertCount(1, $field->siblings());
		$this->assertSame($field, $field->siblings()->first());

		$field = new TestField(
			siblings: new Fields([
				new TestField(name: 'a'),
				new TestField(name: 'b'),
			])
		);

		$this->assertCount(2, $field->siblings());
		$this->assertSame('a', $field->siblings()->first()->name());
		$this->assertSame('b', $field->siblings()->last()->name());
	}

	public function testSubmit(): void
	{
		$field = new TestField();
		$this->assertNull($field->value());
		$field->submit('Test value');
		$this->assertSame('Test value', $field->value());
	}

	public function testToStoredValue(): void
	{
		$field = new TestField();
		$field->fill('test');

		$this->assertSame('test', $field->toStoredValue());
	}

	public function testTranslate(): void
	{
		$field = new TestField();
		$this->assertTrue($field->translate());

		$field = new TestField(translate: false);
		$this->assertFalse($field->translate());
	}

	public function testType(): void
	{
		$field = new TestField();
		$this->assertSame('test', $field->type());
	}

	public function testValue(): void
	{
		$field = new TestField();
		$this->assertNull($field->value());

		$field = new TestField(value: 'Test');
		$this->assertSame('Test', $field->value());

		$field = new TestField(default: 'Default value');
		$this->assertNull($field->value());

		$field = new TestField(default: 'Default value');
		$this->assertSame('Default value', $field->value(true));

		$field = new NoValueField(value: 'Test');
		$this->assertNull($field->value());
	}

	public function testWhen(): void
	{
		$field = new TestField();
		$this->assertNull($field->when());

		$field = new TestField(when: ['a' => 'test']);
		$this->assertSame(['a' => 'test'], $field->when());
	}

	public function testWidth(): void
	{
		$field = new TestField();
		$this->assertSame('1/1', $field->width());

		$field = new TestField(width: '1/2');
		$this->assertSame('1/2', $field->width());
	}
}
