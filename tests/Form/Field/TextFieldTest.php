<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class TextFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('text');

		$this->assertSame('text', $field->type());
		$this->assertSame('text', $field->name());
		$this->assertSame('', $field->value());
		$this->assertNull($field->icon());
		$this->assertNull($field->placeholder());
		$this->assertTrue($field->counter());
		$this->assertNull($field->maxlength());
		$this->assertNull($field->minlength());
		$this->assertNull($field->pattern());
		$this->assertTrue($field->spellcheck());
		$this->assertTrue($field->save());
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
