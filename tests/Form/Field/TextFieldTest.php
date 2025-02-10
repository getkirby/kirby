<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;

class TextFieldTest extends TestCase
{
	public function testDefaultProps()
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
		$this->assertFalse($field->spellcheck());
		$this->assertTrue($field->save());
	}

	public static function converterDataProvider(): array
	{
		return [
			['slug', 'Super nice', 'super-nice'],
			['upper', 'Super nice', 'SUPER NICE'],
			['lower', 'Super nice', 'super nice'],
			['ucfirst', 'super nice', 'Super nice'],
			['upper', null, ''],
			['lower', '', ''],
		];
	}

	/**
	 * @dataProvider converterDataProvider
	 */
	public function testConverter($converter, $input, $expected)
	{
		$field = $this->field('text', [
			'converter' => $converter,
			'value'     => $input,
			'default'   => $input
		]);

		$this->assertSame($expected, $field->value());
		$this->assertSame($expected, $field->default());
	}

	public function testInvalidConverter()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid converter "does-not-exist"');

		$field = $this->field('text', [
			'converter' => 'does-not-exist',
		]);
	}

	public function testMinLength()
	{
		$field = $this->field('text', [
			'value' => 'test',
			'minlength' => 5
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('minlength', $field->errors());
	}

	public function testMaxLength()
	{
		$field = $this->field('text', [
			'value'     => 'test',
			'maxlength' => 3
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('maxlength', $field->errors());
	}
}
