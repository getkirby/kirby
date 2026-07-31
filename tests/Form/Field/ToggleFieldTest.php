<?php

namespace Kirby\Form\Field;

use Kirby\Toolkit\HtmlString;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ToggleField::class)]
class ToggleFieldTest extends TestCase
{
	public function testDefault(): void
	{
		// true
		$field = $this->field('toggle', [
			'default' => true
		]);

		$this->assertTrue($field->default() === true);

		// false
		$field = $this->field('toggle', [
			'default' => false
		]);

		$this->assertTrue($field->default() === false);
	}

	public function testEmptyValue(): void
	{
		$field = $this->field('toggle');

		$this->assertNull($field->emptyValue());

		// an untouched field, a reset field and a field filled with its
		// own stored value must all produce the same form value
		$this->assertFalse($field->toFormValue());

		$field->reset();

		$this->assertFalse($field->toFormValue());
		$this->assertFalse($field->toStoredValue());

		$field->fill($field->toStoredValue());

		$this->assertFalse($field->toFormValue());
	}

	public function testFill(): void
	{
		$field = $this->field('toggle');

		$field->fill(true);
		$this->assertTrue($field->toFormValue());

		$field->fill('true');
		$this->assertTrue($field->toFormValue());

		$field->fill('on');
		$this->assertTrue($field->toFormValue());

		$field->fill(false);
		$this->assertFalse($field->toFormValue());

		$field->fill('false');
		$this->assertFalse($field->toFormValue());

		$field->fill('off');
		$this->assertFalse($field->toFormValue());
	}

	public function testProps(): void
	{
		$field = $this->field('toggle');
		$props = $field->props();

		ksort($props);

		$expected = [
			'after'       => null,
			'autofocus'   => false,
			'before'      => null,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'icon'        => null,
			'label'       => 'Toggle',
			'name'        => 'toggle',
			'required'    => false,
			'saveable'    => true,
			'text' 	      => null,
			'translate'   => true,
			'type'        => 'toggle',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testRequired(): void
	{
		$field = $this->field('toggle');
		$this->assertFalse($field->isRequired());
		$this->assertFalse($field->toFormValue());
		$this->assertFalse($field->isInvalid());
		$this->assertSame([], $field->errors());

		$field = $this->field('toggle', [
			'required' => true
		]);
		$this->assertTrue($field->isRequired());
		$this->assertFalse($field->toFormValue());
		$this->assertTrue($field->isInvalid());
		$this->assertSame(['required' => 'The field is required'], $field->errors());
	}

	public function testText(): void
	{
		$field = $this->field('toggle', [
			'text' => 'Yay {{ page.slug }}'
		]);

		$this->assertInstanceOf(HtmlString::class, $field->text());
		$this->assertSame('Yay test', (string)$field->text());
	}

	public function testTextWithTranslation(): void
	{
		$props = [
			'text' => [
				'en' => 'Yay {{ page.slug }}',
				'de' => 'Ja {{ page.slug }}'
			]
		];

		I18n::$locale = 'en';

		$field = $this->field('toggle', $props);
		$this->assertSame('Yay test', (string)$field->text());

		I18n::$locale = 'de';

		$field = $this->field('toggle', $props);
		$this->assertSame('Ja test', (string)$field->text());
	}

	public function testTextToggle(): void
	{
		$field = $this->field('toggle', [
			'text' => [
				'Yes {{ page.slug }}',
				'No {{ page.slug }}'
			]
		]);

		$text = $field->text();

		$this->assertInstanceOf(HtmlString::class, $text[0]);
		$this->assertSame(['Yes test', 'No test'], array_map(strval(...), $text));
	}

	public function testTextToggleWithTranslation(): void
	{
		$props = [
			'text' => [
				['en' => 'Yes {{ page.slug }}', 'de' => 'Ja {{ page.slug }}'],
				['en' => 'No {{ page.slug }}', 'de' => 'Nein {{ page.slug }}']
			]
		];

		I18n::$locale = 'en';

		$field = $this->field('toggle', $props);
		$this->assertSame(
			['Yes test', 'No test'],
			array_map(strval(...), $field->text())
		);

		I18n::$locale = 'de';

		$field = $this->field('toggle', $props);
		$this->assertSame(
			['Ja test', 'Nein test'],
			array_map(strval(...), $field->text())
		);
	}
}
