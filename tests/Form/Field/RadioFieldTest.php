<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RadioField::class)]
class RadioFieldTest extends TestCase
{
	public function testProps(): void
	{
		$field = $this->field('radio');

		$this->assertSame('radio', $field->type());
		$this->assertSame('radio', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame([], $field->options());
		$this->assertTrue($field->hasValue());
	}

	public function testValidations(): void
	{
		$field = $this->field('radio', [
			'options' => [
				'one'   => 'Option One',
				'two'   => 'Option Two',
				'three' => 'Option Three',
			],
			'value' => 'one',
		]);

		$this->assertTrue($field->isValid());

		$field = $this->field('radio', [
			'options' => [
				'one'   => 'Option One',
				'two'   => 'Option Two',
				'three' => 'Option Three',
			],
			'value' => 'foo',
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame([
			'option' => 'Please select a valid option',
		], $field->errors());
	}
}
