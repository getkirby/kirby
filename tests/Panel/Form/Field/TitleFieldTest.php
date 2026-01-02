<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TitleField::class)]
class TitleFieldTest extends TestCase
{
	public function testProps(): void
	{
		$field = new TitleField();
		$props = $field->props();

		ksort($props);

		$expected = [
			'after'        => null,
			'autocomplete' => null,
			'autofocus'    => false,
			'before'       => null,
			'converter'    => null,
			'counter'      => true,
			'disabled'     => false,
			'font'         => 'sans-serif',
			'help'         => null,
			'hidden'       => false,
			'icon'         => 'title',
			'label'        => 'Title',
			'maxlength'    => null,
			'minlength'    => null,
			'name'         => 'title',
			'pattern'      => null,
			'placeholder'  => null,
			'required'     => false,
			'saveable'     => true,
			'spellcheck'   => null,
			'translate'    => true,
			'type'         => 'text',
			'when'         => null,
			'width'        => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testLabel(): void
	{
		$field = new TitleField(
			label: 'Test'
		);

		$this->assertSame('Test', $field->label());
	}
}
