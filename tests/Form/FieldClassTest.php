<?php

namespace Kirby\Form;

use Exception;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\TestCase;

class TestField extends FieldClass
{
}

class HiddenField extends FieldClass
{
	public function isHidden(): bool
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

/**
 * @coversDefaultClass \Kirby\Form\FieldClass
 */
class FieldClassTest extends TestCase
{
	/**
	 * @covers ::__call
	 */
	public function test__call()
	{
		$field = new TestField([
			'foo' => 'bar'
		]);

		$this->assertSame('bar', $field->foo());
	}

	/**
	 * @covers ::after
	 */
	public function testAfter()
	{
		$field = new TestField();
		$this->assertNull($field->after());

		$field = new TestField(['after' => 'Test']);
		$this->assertSame('Test', $field->after());

		$field = new TestField(['after' => ['en' => 'Test']]);
		$this->assertSame('Test', $field->after());
	}

	/**
	 * @covers ::api
	 */
	public function testApi()
	{
		$field = new TestField();
		$this->assertSame([], $field->api());
	}

	/**
	 * @covers ::autofocus
	 */
	public function testAutofocus()
	{
		$field = new TestField();
		$this->assertFalse($field->autofocus());

		$field = new TestField(['autofocus' => true]);
		$this->assertTrue($field->autofocus());
	}

	/**
	 * @covers ::before
	 */
	public function testBefore()
	{
		$field = new TestField();
		$this->assertNull($field->before());

		$field = new TestField(['before' => 'Test']);
		$this->assertSame('Test', $field->before());

		$field = new TestField(['before' => ['en' => 'Test']]);
		$this->assertSame('Test', $field->before());
	}

	/**
	 * @covers ::data
	 */
	public function testData()
	{
		$field = new TestField();
		$this->assertNull($field->data());

		// use default value
		$field = new TestField(['default' => 'default value']);
		$this->assertSame('default value', $field->data(true));

		// don't use default value
		$field = new TestField(['default' => 'default value']);
		$this->assertNull($field->data());

		// use existing value
		$field = new TestField(['value' => 'test']);
		$this->assertSame('test', $field->data());
	}

	/**
	 * @covers ::default
	 */
	public function testDefault()
	{
		$field = new TestField();
		$this->assertNull($field->default());

		// simple default value
		$field = new TestField(['default' => 'Test']);
		$this->assertSame('Test', $field->default());

		// default value from string template
		$field = new TestField([
			'model' => new Page([
				'slug'    => 'test',
				'content' => [
					'title' => 'Test title'
				]
			]),
			'default' => '{{ page.title }}'
		]);

		$this->assertSame('Test title', $field->default());
	}

	/**
	 * @covers ::dialogs
	 */
	public function testDialogs()
	{
		$field = new TestField();
		$this->assertSame([], $field->dialogs());
	}

	/**
	 * @covers ::disabled
	 * @covers ::isDisabled
	 */
	public function testDisabled()
	{
		$field = new TestField();
		$this->assertFalse($field->disabled());
		$this->assertFalse($field->isDisabled());

		$field = new TestField(['disabled' => true]);
		$this->assertTrue($field->disabled());
		$this->assertTrue($field->isDisabled());
	}

	/**
	 * @covers ::drawers
	 */
	public function testDrawers()
	{
		$field = new TestField();
		$this->assertSame([], $field->drawers());
	}

	/**
	 * @covers ::errors
	 * @covers ::validate
	 * @covers ::validations
	 */
	public function testErrors()
	{
		$field = new TestField();
		$this->assertSame([], $field->errors());

		$field = new TestField(['required' => true]);
		$this->assertSame(['required' => 'Please enter something'], $field->errors());

		$field = new ValidatedField(['value' => 'a']);
		$this->assertSame([], $field->errors());

		$field = new ValidatedField(['value' => 'a', 'minlength' => 4]);
		$this->assertSame(['minlength' => 'Please enter a longer value. (min. 4 characters)'], $field->errors());

		$field = new ValidatedField(['value' => 'b']);
		$this->assertSame(['custom' => 'Please enter an a'], $field->errors());
	}

	/**
	 * @covers ::fill
	 */
	public function testFill()
	{
		$field = new TestField();
		$this->assertNull($field->value());
		$field->fill('Test value');
		$this->assertSame('Test value', $field->value());
	}

	/**
	 * @covers ::isEmpty
	 * @covers ::isEmptyValue
	 */
	public function testIsEmpty()
	{
		$field = new TestField();
		$this->assertTrue($field->isEmpty());

		$field = new TestField(['value' => 'Test']);
		$this->assertFalse($field->isEmpty());
	}

	/**
	 * @covers ::isEmptyValue
	 */
	public function testIsEmptyValue()
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

	/**
	 * @covers ::isHidden
	 */
	public function testIsHidden()
	{
		$field = new TestField();
		$this->assertFalse($field->isHidden());

		$field = new HiddenField();
		$this->assertTrue($field->isHidden());
	}

	public function testIsTranslatable()
	{
		$language = Language::ensure('current');

		$field = new TestField();
		$this->assertTrue($field->isTranslatable($language));
	}

	public function testIsTranslatableWithNonDefaultLanguage()
	{
		$language = new Language([
			'code'    => 'de',
			'default' => false
		]);

		$field = new TestField(['translate' => true]);
		$this->assertTrue($field->isTranslatable($language));

		$field = new TestField(['translate' => false]);
		$this->assertFalse($field->isTranslatable($language));
	}

	/**
	 * @covers ::isInvalid
	 * @covers ::isValid
	 */
	public function testInvalid()
	{
		$field = new TestField();
		$this->assertFalse($field->isInvalid());

		$field = new TestField(['required' => true]);
		$this->assertTrue($field->isInvalid());

		$field = new TestField(['required' => true, 'value' => 'Test']);
		$this->assertFalse($field->isInvalid());
	}

	/**
	 * @covers ::isRequired
	 * @covers ::required
	 */
	public function testIsRequired()
	{
		$field = new TestField();
		$this->assertFalse($field->isRequired());
		$this->assertFalse($field->required());

		$field = new TestField(['required' => true]);
		$this->assertTrue($field->isRequired());
		$this->assertTrue($field->required());
	}

	public function testIsSubmittable()
	{
		$language = Language::ensure('current');

		$field = new TestField();
		$this->assertTrue($field->isSubmittable($language));

		$field = new NoValueField();
		$this->assertFalse($field->isSubmittable($language));
	}

	public function testIsSubmittableWithDisabledField()
	{
		$language = Language::ensure('current');

		$field = new TestField(['disabled' => true]);
		$this->assertFalse($field->isSubmittable($language));
	}

	public function testIsSubmittableWithNonDefaultLanguage()
	{
		$language = new Language([
			'code'    => 'de',
			'default' => false
		]);

		$field = new TestField(['translate' => true]);
		$this->assertTrue($field->isSubmittable($language));

		$field = new TestField(['translate' => false]);
		$this->assertFalse($field->isSubmittable($language));
	}

	public function testIsSubmittableWithWhenQueryAndMatchingValue()
	{
		$language = Language::ensure('current');

		$siblings = new Fields([
			new TestField(['name' => 'a', 'value' => 'b']),
		]);

		$field = new TestField([
			'siblings' => $siblings,
			'when'     => [
				'a' => 'b'
			],
		]);

		$this->assertTrue($field->isSubmittable($language));
	}

	public function testIsSubmittableWithWhenQueryAndNonMatchingValue()
	{
		$language = Language::ensure('current');

		$siblings = new Fields([
			new TestField(['name' => 'a', 'value' => 'something-else']),
		]);

		$field = new TestField([
			'siblings' => $siblings,
			'when'     => [
				'a' => 'b'
			],
		]);

		$this->assertFalse($field->isSubmittable($language));
	}

	/**
	 * @covers ::hasValue
	 */
	public function testHasValue()
	{
		$field = new TestField();
		$this->assertTrue($field->hasValue());

		$field = new NoValueField();
		$this->assertFalse($field->hasValue());
	}

	/**
	 * @covers ::help
	 */
	public function testHelp()
	{
		$field = new TestField();
		$this->assertNull($field->help());

		// regular help
		$field = new TestField(['help' => 'Test']);
		$this->assertSame('<p>Test</p>', $field->help());

		// translated help
		$field = new TestField(['help' => ['en' => 'Test']]);
		$this->assertSame('<p>Test</p>', $field->help());

		// help from string template
		$field = new TestField([
			'model' => new Page([
				'slug'    => 'test',
				'content' => [
					'title' => 'Test title'
				]
			]),
			'help' => 'A field for {{ page.title }}'
		]);

		$this->assertSame('<p>A field for Test title</p>', $field->help());
	}

	/**
	 * @covers ::icon
	 */
	public function testIcon()
	{
		$field = new TestField();
		$this->assertNull($field->icon());

		$field = new TestField(['icon' => 'Test']);
		$this->assertSame('Test', $field->icon());
	}

	/**
	 * @covers ::id
	 */
	public function testId()
	{
		$field = new TestField();
		$this->assertSame('test', $field->id());

		$field = new TestField(['name' => 'test-id']);
		$this->assertSame('test-id', $field->id());
	}

	/**
	 * @covers ::kirby
	 */
	public function testKirby()
	{
		$field = new TestField();
		$this->assertSame(kirby(), $field->kirby());
	}

	/**
	 * @covers ::label
	 */
	public function testLabel()
	{
		$field = new TestField();
		$this->assertSame('Test', $field->label());

		$field = new TestField(['label' => 'Test']);
		$this->assertSame('Test', $field->label());

		$field = new TestField(['label' => ['en' => 'Test']]);
		$this->assertSame('Test', $field->label());
	}

	/**
	 * @covers ::model
	 */
	public function testModel()
	{
		$field = new TestField();
		$site  = site();
		$this->assertIsSite($site, $field->model());

		$page  = new Page(['slug' => 'test']);
		$field = new TestField(['model' => $page]);
		$this->assertIsPage($page, $field->model());
	}

	/**
	 * @covers ::name
	 */
	public function testName()
	{
		$field = new TestField();
		$this->assertSame('test', $field->name());

		$field = new TestField(['name' => 'test-name']);
		$this->assertSame('test-name', $field->name());
	}

	/**
	 * @covers ::params
	 */
	public function testParams()
	{
		$field = new TestField($params = [
			'foo'      => 'bar',
			'name'     => 'Test name',
			'required' => true
		]);

		$this->assertSame($params, $field->params());
	}

	/**
	 * @covers ::placeholder
	 */
	public function testPlaceholder()
	{
		$field = new TestField();
		$this->assertNull($field->placeholder());

		// regular placeholder
		$field = new TestField(['placeholder' => 'Test']);
		$this->assertSame('Test', $field->placeholder());

		// translated placeholder
		$field = new TestField(['placeholder' => ['en' => 'Test']]);
		$this->assertSame('Test', $field->placeholder());

		// placeholder from string template
		$field = new TestField([
			'model' => new Page([
				'slug'    => 'test',
				'content' => [
					'title' => 'Test title'
				]
			]),
			'placeholder' => 'Placeholder for {{ page.title }}'
		]);

		$this->assertSame('Placeholder for Test title', $field->placeholder());
	}

	/**
	 * @covers ::props
	 * @covers ::toArray
	 */
	public function testProps()
	{
		$field = new TestField($props = [
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
			'saveable'    => true,
			'translate'   => false,
			'type'        => 'test',
			'when'        => ['a' => 'b'],
			'width'       => '1/2'
		]);

		$props['help'] = '<p>Help value</p>';

		$array = $field->toArray();

		$this->assertSame($props, $field->props());
	}

	/**
	 * @covers ::routes
	 */
	public function testRoutes()
	{
		$field = new TestField();
		$this->assertSame([], $field->routes());
	}

	/**
	 * @covers ::save
	 */
	public function testSave()
	{
		$field = new TestField();
		$this->assertTrue($field->save());
	}

	/**
	 * @covers ::siblings
	 */
	public function testSiblings()
	{
		$field = new TestField();
		$this->assertInstanceOf(Fields::class, $field->siblings());
		$this->assertCount(1, $field->siblings());
		$this->assertSame($field, $field->siblings()->first());

		$field = new TestField([
			'siblings' => new Fields([
				new TestField(['name' => 'a']),
				new TestField(['name' => 'b']),
			])
		]);

		$this->assertCount(2, $field->siblings());
		$this->assertSame('a', $field->siblings()->first()->name());
		$this->assertSame('b', $field->siblings()->last()->name());
	}

	/**
	 * @covers ::toStoredValue
	 */
	public function testToStoredValue()
	{
		$field = new TestField();
		$field->fill('test');

		$this->assertSame('test', $field->toStoredValue());
	}

	/**
	 * @covers ::translate
	 */
	public function testTranslate()
	{
		$field = new TestField();
		$this->assertTrue($field->translate());

		$field = new TestField(['translate' => false]);
		$this->assertFalse($field->translate());
	}

	/**
	 * @covers ::type
	 */
	public function testType()
	{
		$field = new TestField();
		$this->assertSame('test', $field->type());
	}

	/**
	 * @covers ::value
	 */
	public function testValue()
	{
		$field = new TestField();
		$this->assertNull($field->value());

		$field = new TestField(['value' => 'Test']);
		$this->assertSame('Test', $field->value());

		$field = new TestField(['default' => 'Default value']);
		$this->assertNull($field->value());

		$field = new TestField(['default' => 'Default value']);
		$this->assertSame('Default value', $field->value(true));

		$field = new NoValueField(['value' => 'Test']);
		$this->assertNull($field->value());
	}

	/**
	 * @covers ::when
	 */
	public function testWhen()
	{
		$field = new TestField();
		$this->assertNull($field->when());

		$field = new TestField(['when' => ['a' => 'test']]);
		$this->assertSame(['a' => 'test'], $field->when());
	}

	/**
	 * @covers ::width
	 */
	public function testWidth()
	{
		$field = new TestField();
		$this->assertSame('1/1', $field->width());

		$field = new TestField(['width' => '1/2']);
		$this->assertSame('1/2', $field->width());
	}
}
