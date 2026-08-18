<?php

namespace Kirby\Form\Field;

use Exception;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Form\Fields;
use Kirby\Form\Mixin;
use Kirby\TestCase as BaseTestCase;
use Kirby\Toolkit\HtmlString;
use PHPUnit\Framework\Attributes\CoversClass;

class TestField extends InputField
{
	use Mixin\After;
	use Mixin\Before;
	use Mixin\Icon;
	use Mixin\Placeholder;

	protected mixed $value = null;

	public function __construct(
		array|string|null $after = null,
		bool|null $autofocus = null,
		array|string|null $before = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		string|null $icon = null,
		array|string|null $label = null,
		string|null $name = null,
		array|string|null $placeholder = null,
		bool|null $required = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default: $default,
			disabled: $disabled,
			help: $help,
			label: $label,
			name: $name,
			required: $required,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->after       = $after;
		$this->before      = $before;
		$this->icon        = $icon;
		$this->placeholder = $placeholder;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'after'       => $this->after(),
			'before'      => $this->before(),
			'icon'        => $this->icon(),
			'placeholder' => $this->placeholder()
		];
	}
}

class HiddenTestField extends TestField
{
	public function isHidden(): bool
	{
		return true;
	}
}

class ValidatedField extends TestField
{
	use Mixin\Minlength;

	public function __construct(
		int|null $minlength = null,
		...$props
	) {
		parent::__construct(...$props);

		$this->minlength = $minlength;
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

#[CoversClass(InputField::class)]
class InputFieldTest extends BaseTestCase
{
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

	public function testDefault(): void
	{
		$field = new TestField();
		$this->assertNull($field->default());

		// simple default value
		$field = new TestField(default: 'Test');
		$this->assertSame('Test', $field->default());

		// default value from string template
		$field = new TestField(
			default: '{{ page.title }}'
		);

		$field->setModel(new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Test title'
			]
		]));

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

	public function testEmptyValue(): void
	{
		$field = new TestField();
		$this->assertNull($field->emptyValue());
	}

	public function testErrors(): void
	{
		$field = new TestField();
		$this->assertSame([], $field->errors());

		$field = new TestField(required: true);
		$this->assertSame(['required' => 'Please enter something'], $field->errors());

		$field = new ValidatedField();
		$field->fill('a');
		$this->assertSame([], $field->errors());

		$field = new ValidatedField(minlength: 4);
		$field->fill('a');
		$this->assertSame(['minlength' => 'Please enter a longer value. (min. 4 characters)'], $field->errors());

		$field = new ValidatedField();
		$field->fill('b');
		$this->assertSame(['custom' => 'Please enter an a'], $field->errors());
	}

	public function testFill(): void
	{
		$field = new TestField();
		$this->assertNull($field->value());
		$field->fill('Test value');
		$this->assertSame('Test value', $field->value());
	}

	public function testHasValue(): void
	{
		$field = new TestField();
		$this->assertTrue($field->hasValue());
	}

	public function testHelp(): void
	{
		$field = new TestField();
		$this->assertNull($field->help());

		// regular help
		$field = new TestField(help: 'Test');
		$this->assertSame('<p>Test</p>', (string)$field->help());

		// translated help
		$field = new TestField(help: ['en' => 'Test']);
		$this->assertSame('<p>Test</p>', (string)$field->help());

		// help from string template
		$field = new TestField(
			help: 'A field for {{ page.title }}'
		);

		$field->setModel(new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Test title'
			]
		]));

		$this->assertSame('<p>A field for Test title</p>', (string)$field->help());
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

	public function testIsEmpty(): void
	{
		$field = new TestField();
		$this->assertTrue($field->isEmpty());

		$field = new TestField();
		$field->fill('Test');
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

	public function testIsHidden(): void
	{
		$field = new TestField();
		$this->assertFalse($field->isHidden());

		$field = new HiddenTestField();
		$this->assertTrue($field->isHidden());
	}

	public function testIsInvalid(): void
	{
		$field = new TestField();
		$this->assertFalse($field->isInvalid());

		$field = new TestField(required: true);
		$this->assertTrue($field->isInvalid());

		$field = new TestField(required: true);
		$field->fill('Test');
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

		$field = new TestField(translate: true);
		$this->assertTrue($field->isStorable($language));

		$field = new TestField(translate: false);
		$this->assertFalse($field->isStorable($language));
	}

	public function testIsSubmittable(): void
	{
		$language = Language::ensure('current');

		$field = new TestField();
		$this->assertTrue($field->isSubmittable($language));
	}

	public function testIsSubmittableWithDisabledField(): void
	{
		$language = Language::ensure('current');

		$field = new TestField(disabled: true);
		$this->assertTrue($field->isSubmittable($language));
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
			(new TestField(name: 'a'))->fill('b'),
		]);

		$field = new TestField(
			when: ['a' => 'b']
		);

		$field->setSiblings($siblings);

		$this->assertTrue($field->isSubmittable($language));
	}

	public function testIsSubmittableWithWhenQueryAndNonMatchingValue(): void
	{
		$language = Language::ensure('current');

		$siblings = new Fields([
			(new TestField(name: 'a'))->fill('something else'),
		]);

		$field = new TestField(
			when: ['a' => 'b']
		);

		$field->setSiblings($siblings);

		$this->assertTrue($field->isSubmittable($language));
	}

	public function testIsSubmittableWithWhenQueryAndTwoFields()
	{
		$language = Language::ensure('current');

		$fields = new Fields([
			new TestField(
				name:  'a',
				when:  ['b' => 'b']
			),
			new TestField(
				name:  'b',
				when:  ['a' => 'a']
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
		$site  = site();
		$this->assertIsSite($site, $field->model());

		$page  = new Page(['slug' => 'test']);
		$field = (new TestField())->setModel($page);
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
			placeholder: 'Placeholder for {{ page.title }}'
		);

		$field->setModel(
			new Page([
				'slug'    => 'test',
				'content' => [
					'title' => 'Test title'
				]
			]),
		);

		$this->assertSame('Placeholder for Test title', $field->placeholder());
	}

	public function testProps(): void
	{
		$field = new TestField(
			after:       $after = 'After value',
			autofocus:   true,
			before:      $before = 'Before value',
			default:     'Default value',
			disabled:    false,
			help:        'Help value',
			icon:        $icon = 'Icon value',
			label:       $label = 'Label value',
			name:        $name = 'name-value',
			placeholder: $placeholder = 'Placeholder value',
			required:    true,
			translate:   false,
			when:        $when = ['a' => 'b'],
			width:       $width = '1/2'
		);

		$this->assertEquals([ // @phpstan-ignore-line
			'after'       => $after,
			'autofocus'   => true,
			'before'      => $before,
			'disabled'    => false,
			'help'        => new HtmlString('<p>Help value</p>'),
			'hidden'      => false,
			'icon'        => $icon,
			'label'       => $label,
			'name'        => $name,
			'placeholder' => $placeholder,
			'required'    => true,
			'saveable'    => true,
			'translate'   => false,
			'type'        => 'test',
			'when'        => $when,
			'width'       => $width
		], $field->props());
	}

	public function testSiblings(): void
	{
		$field = new TestField();
		$this->assertInstanceOf(Fields::class, $field->siblings());
		$this->assertCount(1, $field->siblings());
		$this->assertSame($field, $field->siblings()->first());

		$field = new TestField();
		$field->setSiblings(new Fields([
			new TestField(name: 'a'),
			new TestField(name: 'b'),
		]));

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

		$field = new TestField();
		$field->fill('Test');
		$this->assertSame('Test', $field->value());

		$field = new TestField(default: 'Default value');
		$this->assertNull($field->value());

		$field = new TestField(default: 'Default value');
		$this->assertSame('Default value', $field->value(true));
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
