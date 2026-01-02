<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PasswordField::class)]
class PasswordFieldTest extends TestCase
{
	public function testProps(): void
	{
		$field = $this->field('password');
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
			'icon'         => 'key',
			'label'        => 'Password',
			'maxlength'    => null,
			'minlength'    => null,
			'name'         => 'password',
			'pattern'      => null,
			'placeholder'  => null,
			'required'     => false,
			'saveable'     => true,
			'spellcheck'   => null,
			'translate'    => true,
			'type'         => 'password',
			'when'         => null,
			'width'        => '1/1',
		];

		$this->assertSame($expected, $props);
	}
}
