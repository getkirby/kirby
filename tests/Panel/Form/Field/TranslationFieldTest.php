<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TranslationField::class)]
class TranslationFieldTest extends TestCase
{
	public function testProps(): void
	{
		$field = new TranslationField();
		$props = $field->props();

		ksort($props);

		// test options separately
		unset($props['options']);

		$expected = [
			'autofocus'   => false,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'icon'        => 'translate',
			'label'       => 'Language',
			'name'        => 'translation',
			'placeholder' => '—',
			'required'    => false,
			'saveable'    => true,
			'translate'   => true,
			'type'        => 'select',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
		$this->assertCount($this->app->translations()->count(), $field->options());
	}

	public function testLabel(): void
	{
		$field = new TranslationField(
			label: 'Test'
		);

		$this->assertSame('Test', $field->label());
	}
}
