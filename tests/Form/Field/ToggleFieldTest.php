<?php

namespace Kirby\Form\Field;

use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ToggleField::class)]
class ToggleFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('toggle');

		$this->assertSame('toggle', $field->type());
		$this->assertSame('toggle', $field->name());
		$this->assertFalse($field->value());
		$this->assertTrue($field->save());
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

		$this->assertSame('Yay test', $field->text());
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
		$this->assertSame('Yay test', $field->text());

		I18n::$locale = 'de';

		$field = $this->field('toggle', $props);
		$this->assertSame('Ja test', $field->text());
	}

	public function testBooleanDefaultValue(): void
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

	public function testTextToggle(): void
	{
		$field = $this->field('toggle', [
			'text' => [
				'Yes {{ page.slug }}',
				'No {{ page.slug }}'
			]
		]);

		$this->assertSame(['Yes test', 'No test'], $field->text());
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
		$this->assertSame(['Yes test', 'No test'], $field->text());

		I18n::$locale = 'de';

		$field = $this->field('toggle', $props);
		$this->assertSame(['Ja test', 'Nein test'], $field->text());
	}
}
