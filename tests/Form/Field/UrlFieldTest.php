<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UrlField::class)]
class UrlFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('url');
		$props = $field->props();

		ksort($props);

		$expected = [
			'after'        => null,
			'autocomplete' => 'url',
			'autofocus'    => false,
			'before'       => null,
			'converter'    => null,
			'counter'      => false,
			'default'      => null,
			'disabled'     => false,
			'font'         => 'sans-serif',
			'help'         => null,
			'hidden'       => false,
			'icon'         => 'url',
			'label'        => 'Url',
			'maxlength'    => null,
			'minlength'    => null,
			'name'         => 'url',
			'pattern'      => null,
			'placeholder'  => 'https://example.com',
			'required'     => false,
			'saveable'     => true,
			'spellcheck'   => null,
			'translate'    => true,
			'type'         => 'url',
			'when'         => null,
			'width'        => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testUrlValidation(): void
	{
		$field = $this->field('url', [
			'value' => 'https://getkirby.com'
		]);

		$this->assertTrue($field->isValid());

		$field = $this->field('url', [
			'value' => 'getkirby.com'
		]);

		$this->assertFalse($field->isValid());
	}

	public function testMinLength(): void
	{
		$field = $this->field('url', [
			'value' => 'https://test.com',
			'minlength' => 17
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('minlength', $field->errors());
	}

	public function testMaxLength(): void
	{
		$field = $this->field('url', [
			'value'     => 'https://test.com',
			'maxlength' => 15
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('maxlength', $field->errors());
	}
}
