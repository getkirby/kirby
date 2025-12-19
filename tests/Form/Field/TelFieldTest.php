<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TelField::class)]
class TelFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('tel');
		$props = $field->props();

		ksort($props);

		$expected = [
			'after'        => null,
			'autocomplete' => 'tel',
			'autofocus'    => false,
			'before'       => null,
			'converter'    => null,
			'counter'      => false,
			'disabled'     => false,
			'font'         => 'sans-serif',
			'help'         => null,
			'hidden'       => false,
			'icon'         => 'phone',
			'label'        => 'Tel',
			'maxlength'    => null,
			'minlength'    => null,
			'name'         => 'tel',
			'pattern'      => null,
			'placeholder'  => null,
			'required'     => false,
			'saveable'     => true,
			'spellcheck'   => null,
			'translate'    => true,
			'type'         => 'tel',
			'when'         => null,
			'width'        => '1/1',
		];

		$this->assertSame($expected, $props);
	}
}
