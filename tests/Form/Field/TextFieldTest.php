<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(TextField::class)]
class TextFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('text');
		$props = $field->props();

		ksort($props);

		$expected = [
			'after'        => null,
			'autocomplete' => null,
			'autofocus'    => false,
			'before'       => null,
			'converter'    => null,
			'counter'      => true,
			'default'      => null,
			'disabled'     => false,
			'font'         => 'sans-serif',
			'help'         => null,
			'hidden'       => false,
			'icon'         => null,
			'label'        => 'Text',
			'maxlength'    => null,
			'minlength'    => null,
			'name'         => 'text',
			'pattern'      => null,
			'placeholder'  => null,
			'required'     => false,
			'saveable'     => true,
			'spellcheck'   => true,
			'translate'    => true,
			'type'         => 'text',
			'when'         => null,
			'width'        => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public static function converterDataProvider(): array
	{
		return [
			['slug', 'Super nice', 'super-nice'],
			['upper', 'Super nice', 'SUPER NICE'],
			['lower', 'Super nice', 'super nice'],
			['ucfirst', 'super nice', 'Super nice'],
			['upper', null, null],
			['lower', '', ''],
		];
	}

	#[DataProvider('converterDataProvider')]
	public function testConverter($converter, $input, $expected): void
	{
		$field = $this->field('text', [
			'converter' => $converter,
			'value'     => $input,
			'default'   => $input
		]);

		$this->assertSame($expected, $field->value());
		$this->assertSame($expected, $field->default());
	}

	public function testInvalidConverter(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid converter "does-not-exist"');

		$field = $this->field('text', [
			'converter' => 'does-not-exist',
		]);

		$field->converter();
	}

	public function testMinLength(): void
	{
		$field = $this->field('text', [
			'value' => 'test',
			'minlength' => 5
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('minlength', $field->errors());
	}

	public function testMaxLength(): void
	{
		$field = $this->field('text', [
			'value'     => 'test',
			'maxlength' => 3
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('maxlength', $field->errors());
	}

	public function testReset(): void
	{
		$field = $this->field('text');
		$field->fill('test');

		$this->assertSame('test', $field->toFormValue());

		$field->reset();

		$this->assertSame('', $field->toFormValue());
	}
}
