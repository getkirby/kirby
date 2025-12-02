<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EmailField::class)]
class EmailFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('email');
		$props = $field->props();

		ksort($props);

		$expected = [
			'after'        => null,
			'autocomplete' => 'email',
			'autofocus'    => false,
			'before'       => null,
			'converter'    => null,
			'counter'      => false,
			'default'      => null,
			'disabled'     => false,
			'font'         => 'sans-serif',
			'help'         => null,
			'hidden'       => false,
			'icon'         => 'email',
			'label'        => 'Email',
			'maxlength'    => null,
			'minlength'    => null,
			'name'         => 'email',
			'pattern'      => null,
			'placeholder'  => 'mail@example.com',
			'required'     => false,
			'saveable'     => true,
			'spellcheck'   => null,
			'translate'    => true,
			'type'         => 'email',
			'when'         => null,
			'width'        => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testEmailValidation(): void
	{
		$field = $this->field('email', [
			'value' => 'mail@getkirby.com'
		]);

		$this->assertTrue($field->isValid());

		$field = $this->field('email', [
			'value' => 'mail[at]getkirby.com'
		]);

		$this->assertFalse($field->isValid());
	}

	public function testMinLength(): void
	{
		$field = $this->field('email', [
			'value' => 'mail@test.com',
			'minlength' => 14
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('minlength', $field->errors());
	}

	public function testMaxLength(): void
	{
		$field = $this->field('email', [
			'value'     => 'mail@test.com',
			'maxlength' => 12
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('maxlength', $field->errors());
	}
}
