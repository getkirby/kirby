<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\TestCase;

abstract class FieldTestCase extends TestCase
{
	protected ModelWithContent $model;

	public function setUp(): void
	{
		$this->setUpSingleLanguage([
			'children' => [
				[
					'slug' => 'test'
				]
			]
		]);

		$this->model = $this->app->page('test');
		$this->setUpTmp();
	}

	public function tearDown(): void
	{
		App::destroy();
		$this->tearDownTmp();
	}

	public static function apiRoutes(): array
	{
		return [
			[
				'pattern' => '/',
				'action'  => fn () => 'Hello world',
			]
		];
	}

	public static function dialogRoutes(): array
	{
		return [
			[
				'pattern' => '/',
				'load'    => fn () => 'loaded',
				'submit'  => fn () => 'submitted'
			]
		];
	}

	public static function drawerRoutes(): array
	{
		return [
			[
				'pattern' => '/',
				'load'    => fn () => 'loaded',
				'submit'  => fn () => 'submitted'
			]
		];
	}

	abstract protected function field(
		array $props = [],
		ModelWithContent|null $model = null
	): Field|FieldClass;

	abstract protected function fieldWithApiRoutes(): Field|FieldClass;
	abstract protected function fieldWithComputedValue(): Field|FieldClass;
	abstract protected function fieldWithCustomStoreHandler(): Field|FieldClass;
	abstract protected function fieldWithDefaultIcon(): Field|FieldClass;
	abstract protected function fieldWithDialogs(): Field|FieldClass;
	abstract protected function fieldWithDrawers(): Field|FieldClass;
	abstract protected function fieldWithHiddenFlag(): Field|FieldClass;
	abstract protected function fieldWithUnsaveableFlag(): Field|FieldClass;

	/**
	 * @covers ::after
	 */
	public function testAfter()
	{
		$field = $this->field(
			props: [
				'after' => 'test'
			]
		);

		$this->assertSame('test', $field->after());
	}

	/**
	 * @covers ::after
	 */
	public function testAfterWhenNotSet()
	{
		$field = $this->field();
		$this->assertNull($field->after());
	}

	/**
	 * @covers ::after
	 */
	public function testAfterMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$field = $this->field(
			props: [
				'after' => [
					'en' => 'en',
					'de' => 'de',
				]
			]
		);

		$this->assertSame('en', $field->after());
	}

	/**
	 * @covers ::after
	 */
	public function testAfterWithQuery()
	{
		$field = $this->field(
			props: [
				'after' => '{{ page.slug }}',
			],
			model: new Page(['slug' => 'blog'])
		);

		$this->assertSame('blog', $field->after());
	}

	/**
	 * @covers ::api
	 * @covers ::routes
	 */
	public function testApi()
	{
		$field    = $this->fieldWithApiRoutes();
		$route    = $field->api()[0];
		$expected = $this->apiRoutes()[0];

		$this->assertSame($expected['pattern'], $route['pattern']);
		$this->assertSame($expected['action'](), $route['action']());
	}

	/**
	 * @covers ::api
	 * @covers ::routes
	 */
	public function testApiWithoutRoutes()
	{
		$field = $this->field();
		$this->assertSame([], $field->api());
	}

	/**
	 * @covers ::autofocus
	 */
	public function testAutofocus()
	{
		$field = $this->field();
		$this->assertFalse($field->autofocus());

		$field = $this->field(props: ['autofocus' => true]);
		$this->assertTrue($field->autofocus());
	}

	/**
	 * @covers ::before
	 */
	public function testBefore()
	{
		$field = $this->field(
			props: [
				'before' => 'test'
			]
		);

		$this->assertSame('test', $field->before());
	}

	/**
	 * @covers ::before
	 */
	public function testBeforeWhenNotSet()
	{
		$field = $this->field();
		$this->assertNull($field->before());
	}

	/**
	 * @covers ::before
	 */
	public function testBeforeMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$field = $this->field(
			props: [
				'before' => [
					'en' => 'en',
					'de' => 'de',
				]
			]
		);

		$this->assertSame('en', $field->before());
	}

	/**
	 * @covers ::before
	 */
	public function testBeforeWithQuery()
	{
		$field = $this->field(
			props: [
				'before' => '{{ page.slug }}',
			]
		);

		$this->assertSame($this->model->slug(), $field->before());
	}

	/**
	 * @covers ::default
	 */
	public function testDefault()
	{
		$field = $this->field(
			props: [
				'default' => 'test'
			]
		);

		$this->assertSame('test', $field->default());
	}

	/**
	 * @covers ::default
	 */
	public function testDefaultWhenNotSet()
	{
		$field = $this->field();
		$this->assertNull($field->default());
	}

	/**
	 * @covers ::default
	 */
	public function testDefaultWithQuery()
	{
		$field = $this->field(
			props: [
				'default' => '{{ page.slug }}',
			]
		);

		$this->assertSame($this->model->slug(), $field->default());
	}

	/**
	 * @covers ::dialogs
	 */
	public function testDialogs()
	{
		$field    = $this->fieldWithDialogs();
		$route    = $field->dialogs()[0];
		$expected = $this->dialogRoutes()[0];

		$this->assertSame($expected['pattern'], $route['pattern']);
		$this->assertSame($expected['load'](), $route['load']());
		$this->assertSame($expected['submit'](), $route['submit']());
	}

	/**
	 * @covers ::dialogs
	 */
	public function testDialogsWhenNotSet()
	{
		$field = $this->field();
		$this->assertSame([], $field->dialogs());
	}

	/**
	 * @covers ::disabled
	 */
	public function testDisabled()
	{
		$field = $this->field(
			props: [
				'disabled' => true
			]
		);

		$this->assertTrue($field->disabled());
	}

	/**
	 * @covers ::disabled
	 */
	public function testDisabledWhenNotSet()
	{
		$field = $this->field();
		$this->assertFalse($field->disabled());
	}

	/**
	 * @covers ::drawers
	 */
	public function testDrawers()
	{
		$field    = $this->fieldWithDrawers();
		$route    = $field->drawers()[0];
		$expected = $this->drawerRoutes()[0];

		$this->assertSame($expected['pattern'], $route['pattern']);
		$this->assertSame($expected['load'](), $route['load']());
		$this->assertSame($expected['submit'](), $route['submit']());
	}

	/**
	 * @covers ::drawers
	 */
	public function testDrawersWhenNotSet()
	{
		$field = $this->field();
		$this->assertSame([], $field->drawers());
	}

	/**
	 * @covers ::errors
	 */
	public function testErrors()
	{
		$field = $this->field();
		$this->assertSame([], $field->errors());
	}

	/**
	 * @covers ::errors
	 */
	public function testErrorsWhenRequired()
	{
		$field = $this->field(
			props: [
				'required' => true
			]
		);

		$this->assertSame(['required' => 'Please enter something'], $field->errors());
	}

	/**
	 * @covers ::fill
	 */
	public function testFill()
	{
		$field = $this->field();
		$this->assertNull($field->value());

		$field->fill('Test value');
		$this->assertSame('Test value', $field->value());
	}

	/**
	 * @covers ::fill
	 */
	public function testFillWithComputedValue()
	{
		$field = $this->fieldWithComputedValue();
		$this->assertSame(' computed', $field->computedValue());

		$field->fill('Test value');
		$this->assertSame('Test value computed', $field->computedValue());

		$field->fill('Test value 2');
		$this->assertSame('Test value 2 computed', $field->computedValue());
	}

	/**
	 * @covers ::formFields
	 */
	public function testFormFields()
	{
		$field = $this->field();

		$this->assertInstanceOf(Fields::class, $field->formFields());
		$this->assertSame($field->formFields(), $field->siblings());
	}

	/**
	 * @covers ::help
	 */
	public function testHelp()
	{
		$field = $this->field(
			props: [
				'help' => 'Test'
			]
		);

		$this->assertSame('<p>Test</p>', $field->help());
	}

	/**
	 * @covers ::help
	 */
	public function testHelpWhenNotSet()
	{
		$field = $this->field();
		$this->assertNull($field->help());
	}

	/**
	 * @covers ::help
	 */
	public function testHelpMultilang()
	{
		$field = $this->field(
			props: [
				'help' => [
					'en' => 'en',
					'de' => 'de'
				]
			]
		);

		$this->assertSame('<p>en</p>', $field->help());
	}

	/**
	 * @covers ::icon
	 */
	public function testIcon()
	{
		$field = $this->field(
			props: [
				'icon' => 'test'
			]
		);

		$this->assertSame('test', $field->icon());
	}

	/**
	 * @covers ::icon
	 */
	public function testIconDefault()
	{
		$field = $this->fieldWithDefaultIcon();
		$this->assertSame('test', $field->icon());
	}

	/**
	 * @covers ::icon
	 */
	public function testIconWhenNotSet()
	{
		$field = $this->field();
		$this->assertNull($field->icon());
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
	 * @covers ::isActive
	 */
	public function testIsActive()
	{
		$a = $this->field(
			props: [
				'name' => 'a'
			]
		);

		$b = $this->field(
			props: [
				'name' => 'b',
				'when' => [
					'a' => 'test'
				]
			]
		);

		// attach siblings, otherwise the when queries won't work
		new Fields([
			$a,
			$b
		]);

		$this->assertTrue($a->isActive());
		$this->assertFalse($b->isActive());

		$a->fill('test');

		$this->assertTrue($a->isActive());
		$this->assertTrue($b->isActive());
	}

	/**
	 * @covers ::isActive
	 */
	public function testIsActiveWithTwoRequirements()
	{
		$a = $this->field(
			props: [
				'name' => 'a'
			]
		);

		$b = $this->field(
			props: [
				'name' => 'b'
			]
		);

		$c = $this->field(
			props: [
				'name' => 'c',
				'when' => [
					'a' => 'test a',
					'b' => 'test b'
				]
			]
		);

		// attach siblings, otherwise the when queries won't work
		new Fields([
			$a,
			$b,
			$c
		]);

		$this->assertTrue($a->isActive());
		$this->assertTrue($b->isActive());
		$this->assertFalse($c->isActive());

		$a->fill('test a');

		$this->assertTrue($a->isActive());
		$this->assertTrue($b->isActive());
		$this->assertFalse($c->isActive());

		$b->fill('test b');

		$this->assertTrue($a->isActive());
		$this->assertTrue($b->isActive());
		$this->assertTrue($c->isActive());
	}

	/**
	 * @covers ::isEmpty
	 * @covers ::isEmptyValue
	 * @dataProvider emptyValuesProvider
	 */
	public function testIsEmpty($value, $expected)
	{
		$field = $this->field(
			props: [
				'value' => $value
			]
		);

		$this->assertSame($expected, $field->isEmpty());
		$this->assertSame($expected, $field->isEmptyValue($value));
	}

	/**
	 * @covers ::isHidden
	 */
	public function testIsHidden()
	{
		$field = $this->field();
		$this->assertFalse($field->isHidden());
	}

	/**
	 * @covers ::isHidden
	 */
	public function testIsHiddenWhenFieldIsHidden()
	{
		$field = $this->fieldWithHiddenFlag();
		$this->assertTrue($field->isHidden());
	}

	/**
	 * @covers ::isInvalid
	 */
	public function testIsInvalid()
	{
		$field = $this->field(
			props: [
				'required' => true
			]
		);

		$this->assertTrue($field->isInvalid());
	}

	/**
	 * @covers ::isInvalid
	 */
	public function testIsInvalidWhenValid()
	{
		$field = $this->field();
		$this->assertFalse($field->isInvalid());
	}

	/**
	 * @covers ::isRequired
	 */
	public function testIsRequired()
	{
		$field = $this->field(
			props: [
				'required' => true
			]
		);

		$this->assertTrue($field->isRequired());
	}

	/**
	 * @covers ::isRequired
	 */
	public function testIsRequiredWhenNotRequired()
	{
		$field = $this->field();
		$this->assertFalse($field->isRequired());
	}

	/**
	 * @covers ::isSaveable
	 * @covers ::save
	 */
	public function testIsSaveable()
	{
		$field = $this->field();
		$this->assertTrue($field->isSaveable());
		$this->assertTrue($field->save());
	}

	/**
	 * @covers ::isSaveable
	 * @covers ::save
	 */
	public function testIsSaveableWhenNotSaveable()
	{
		$field = $this->fieldWithUnsaveableFlag();
		$this->assertFalse($field->isSaveable());
		$this->assertFalse($field->save());
	}

	/**
	 * @covers ::isValid
	 */
	public function testIsValid()
	{
		$field = $this->field();
		$this->assertTrue($field->isValid());
	}

	/**
	 * @covers ::isValid
	 */
	public function testIsValidWhenInvalid()
	{
		$field = $this->field(
			props: [
				'required' => true
			]
		);

		$this->assertFalse($field->isValid());
	}

	/**
	 * @covers ::kirby
	 */
	public function testKirby()
	{
		$field = $this->field();
		$this->assertInstanceOf(App::class, $field->kirby());
	}

	/**
	 * @covers ::label
	 */
	public function testLabel()
	{
		$field = $this->field(
			props: [
				'label' => 'test'
			]
		);

		$this->assertSame('test', $field->label());
	}

	/**
	 * @covers ::label
	 */
	public function testLabelMultilang()
	{
		$field = $this->field(
			props: [
				'label' => [
					'en' => 'en',
					'de' => 'de'
				]
			]
		);

		$this->assertSame('en', $field->label());
	}

	/**
	 * @covers ::label
	 */
	public function testLabelWithQuery()
	{
		$field = $this->field(
			props: [
				'label' => '{{ page.slug }}'
			]
		);

		$this->assertSame($this->model->slug(), $field->label());
	}

	/**
	 * @covers ::label
	 */
	public function testLabelWhenNotSet()
	{
		$field = $this->field();

		// fall back to the field type
		$this->assertSame('Test', $field->label());
	}

	/**
	 * @covers ::model
	 */
	public function testModel()
	{
		$field = $this->field();
		$this->assertSame($this->model, $field->model());
	}

	/**
	 * @covers ::name
	 * @covers ::id
	 */
	public function testName()
	{
		$field = $this->field(
			props: [
				'name' => 'the-name'
			]
		);

		$this->assertSame('the-name', $field->name());
		$this->assertSame('the-name', $field->id());
	}

	/**
	 * @covers ::name
	 * @covers ::id
	 */
	public function testNameWhenNotSet()
	{
		$field = $this->field();

		// the field type should be used as name
		$this->assertSame('test', $field->name());
		$this->assertSame('test', $field->id());
	}

	/**
	 * @covers ::needsValue
	 */
	public function testNeedsValue()
	{
		$field = $this->field();
		$this->assertFalse($field->needsValue());
	}

	/**
	 * @covers ::needsValue
	 */
	public function testNeedsValueWhenRequired()
	{
		$field = $this->field(
			props: [
				'required' => true
			]
		);

		$this->assertTrue($field->needsValue());
	}

	/**
	 * @covers ::needsValue
	 */
	public function testNeedsValueWhenRequiredAndNotEmpty()
	{
		$field = $this->field(
			props: [
				'required' => true,
				'value'    => 'Some value'
			]
		);

		$this->assertFalse($field->needsValue());
	}

	/**
	 * @covers ::needsValue
	 */
	public function testNeedsValueWhenRequiredAndNotActive()
	{
		$a = $this->field(
			props: [
				'name' => 'a'
			]
		);

		$b = $this->field(
			props: [
				'name'     => 'b',
				'required' => true,
				'when'     => [
					'a' => 'test'
				]
			]
		);

		// attach siblings, otherwise the when queries won't work
		new Fields([
			$a,
			$b
		]);

		$this->assertFalse($b->needsValue());

		$a->fill('test');

		$this->assertTrue($b->needsValue());

		$b->fill('test');

		$this->assertFalse($b->needsValue());
	}

	/**
	 * @covers ::next
	 * @covers ::siblingsCollection
	 */
	public function testNext()
	{
		$siblings = new Fields([
			$this->field(['name' => 'a']),
			$this->field(['name' => 'b']),
		], $this->model);

		$this->assertSame('b', $siblings->first()->next()->name());
		$this->assertNull($siblings->last()->next());
	}

	/**
	 * @covers ::next
	 * @covers ::siblingsCollection
	 */
	public function testNextWhenNotSet()
	{
		$field = $this->field();
		$this->assertNull($field->next());
	}

	/**
	 * @covers ::placeholder
	 */
	public function testPlaceholder()
	{
		$field = $this->field(
			props: [
				'placeholder' => 'test'
			]
		);

		$this->assertSame('test', $field->placeholder());
	}

	/**
	 * @covers ::placeholder
	 */
	public function testPlaceholderMultilang()
	{
		$field = $this->field(
			props: [
				'placeholder' => [
					'en' => 'en',
					'de' => 'de'
				]
			]
		);

		$this->assertSame('en', $field->placeholder());
	}

	/**
	 * @covers ::placeholder
	 */
	public function testPlaceholderWithQuery()
	{
		$field = $this->field(
			props: [
				'placeholder' => '{{ page.slug }}'
			]
		);

		$this->assertSame($this->model->slug(), $field->placeholder());
	}

	/**
	 * @covers ::placeholder
	 */
	public function testPlaceholderWhenNotSet()
	{
		$field = $this->field();
		$this->assertNull($field->placeholder());
	}

	/**
	 * @covers ::prev
	 * @covers ::siblingsCollection
	 */
	public function testPrev()
	{
		$siblings = new Fields([
			$this->field(['name' => 'a']),
			$this->field(['name' => 'b']),
		], $this->model);

		$this->assertSame('a', $siblings->last()->prev()->name());
		$this->assertNull($siblings->first()->prev());
	}

	/**
	 * @covers ::prev
	 * @covers ::siblingsCollection
	 */
	public function testPrevWhenNotSet()
	{
		$field = $this->field();
		$this->assertNull($field->prev());
	}

	/**
	 * @covers ::siblings
	 */
	public function testSiblings()
	{
		$siblings = new Fields([
			$a = $this->field(['name' => 'a']),
			$b = $this->field(['name' => 'b']),
		], $this->model);

		$this->assertSame($siblings, $a->siblings());
		$this->assertSame($siblings, $b->siblings());
	}

	/**
	 * @covers ::siblings
	 */
	public function testSiblingsWhenNotSet()
	{
		$field = $this->field();

		$this->assertInstanceOf(Fields::class, $field->siblings());

		$this->assertCount(1, $field->siblings());

		$this->assertSame($field, $field->siblings()->first());
		$this->assertSame($field, $field->siblings()->last());
	}

	/**
	 * @covers ::toArray
	 */
	public function testToArray()
	{
		$field    = $this->field();
		$expected = [
			'autofocus' => false,
			'disabled'  => false,
			'hidden'    => false,
			'label'     => 'Test',
			'name'      => 'test',
			'required'  => false,
			'saveable'  => true,
			'translate' => true,
			'type'      => 'test',
			'width'     => '1/1',
		];

		$this->assertSame($expected, $field->toArray());
	}

	/**
	 * @covers ::toFormValue
	 * @covers ::value
	 */
	public function testToFormValue()
	{
		$field = $this->field(
			props: [
				'value' => 'test'
			]
		);

		$this->assertSame('test', $field->toFormValue());
		$this->assertSame('test', $field->value());
	}

	/**
	 * @covers ::toFormValue
	 * @covers ::value
	 */
	public function testToFormValueWhenNotSet()
	{
		$field = $this->field();
		$this->assertNull($field->toFormValue());
		$this->assertNull($field->value());
	}

	/**
	 * @covers ::toFormValue
	 */
	public function testToFormValueWhenUnsaveable()
	{
		$field = $this->fieldWithUnsaveableFlag();
		$field->fill('test');

		$this->assertNull($field->toFormValue());
	}

	/**
	 * @covers ::toFormValue
	 * @covers ::value
	 */
	public function testToFormValueWithDefault()
	{
		$field = $this->field(
			props: [
				'default' => 'default value'
			]
		);

		$this->assertNull($field->toFormValue());
		$this->assertSame('default value', $field->toFormValue(true));
		$this->assertSame('default value', $field->value(true));
	}

	/**
	 * @covers ::toStoredValue
	 */
	public function testToStoredValue()
	{
		$field = $this->field(
			props: [
				'value' => 'test'
			]
		);

		$this->assertSame('test', $field->toStoredValue());
	}

	/**
	 * @covers ::toStoredValue
	 * @covers ::data
	 */
	public function testToStoredValueWhenNotSet()
	{
		$field = $this->field();
		$this->assertNull($field->toStoredValue());
		$this->assertNull($field->data());
	}

	/**
	 * @covers ::toStoredValue
	 * @covers ::data
	 */
	public function testToStoredValueWhenUnsaveable()
	{
		$field = $this->fieldWithUnsaveableFlag();
		$field->fill('test');

		$this->assertNull($field->toStoredValue());
		$this->assertNull($field->data());
	}

	/**
	 * @covers ::toStoredValue
	 * @covers ::data
	 */
	public function testToStoredValueWithDefault()
	{
		$field = $this->field(
			props: [
				'default' => 'default value'
			]
		);

		$this->assertNull($field->toStoredValue());
		$this->assertNull($field->data());
		$this->assertSame('default value', $field->toStoredValue(true));
		$this->assertSame('default value', $field->data(true));
	}

	/**
	 * @covers ::toStoredValue
	 * @covers ::data
	 */
	public function testToStoredValueWithCustomStoreHandler()
	{
		$field = $this->fieldWithCustomStoreHandler();
		$field->fill([
			'a',
			'b',
			'c'
		]);

		$this->assertSame('a,b,c', $field->toStoredValue());
		$this->assertSame('a,b,c', $field->data());
	}

	/**
	 * @covers ::validate
	 * @covers ::validations
	 */
	public function testValidate()
	{
		$field = $this->field();
		$this->assertSame([], $field->validate());
	}

	/**
	 * @covers ::validate
	 * @covers ::validations
	 */
	public function testValidateWithFieldValidations()
	{
		$field = $this->fieldWithValidations();

		$this->assertSame([], $field->validate());

		$field->fill('This is way too long');

		$this->assertSame([
			'maxlength' => 'Please enter a shorter value. (max. 5 characters)',
			'custom'    => 'Please enter an a'
		], $field->validate());

		$field->fill('Not a');

		$this->assertSame([
			'custom' => 'Please enter an a'
		], $field->validate());

		$field->fill('a');

		$this->assertSame([], $field->validate());
	}

	/**
	 * @covers ::validate
	 * @covers ::setValidate
	 */
	public function testValidateWithSingleRule()
	{
		$field = $this->field(
			props: [
				'validate' => 'url'
			]
		);

		$this->assertSame([], $field->validate());

		$field->fill('invalid url');

		$this->assertSame(['url' => 'Please enter a valid URL'], $field->validate());

		$field->fill('https://getkirby.com');

		$this->assertSame([], $field->validate());
	}

	/**
	 * @covers ::validate
	 * @covers ::setValidate
	 */
	public function testValidateWithMultipleRules()
	{
		$field = $this->field(
			props: [
				'validate' => [
					'url',
					'minlength' => 18
				]
			]
		);

		$this->assertSame([], $field->validate());

		$field->fill('invalid url');

		$this->assertSame([
			'url'       => 'Please enter a valid URL',
			'minlength' => 'Please enter a longer value. (min. 18 characters)'
		], $field->validate());

		$field->fill('https://kirby.com');

		$this->assertSame([
			'minlength' => 'Please enter a longer value. (min. 18 characters)'
		], $field->validate());

		$field->fill('https://getkirby.com');

		$this->assertSame([], $field->validate());
	}

	/**
	 * @covers ::width
	 */
	public function testWidth()
	{
		$field = $this->field(
			props: [
				'width' => '1/2'
			]
		);

		$this->assertSame('1/2', $field->width());
	}

	/**
	 * @covers ::width
	 */
	public function testWidthWhenNotSet()
	{
		$field = $this->field();
		$this->assertSame('1/1', $field->width());
	}

}
