<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\DataProvider;

class NumberFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('number');

		$this->assertSame('number', $field->type());
		$this->assertSame('number', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame('', $field->default());
		$this->assertNull($field->min());
		$this->assertNull($field->max());
		$this->assertSame('', $field->step());
		$this->assertTrue($field->save());
	}

	public static function valueProvider(): array
	{
		return [
			[null, ''],
			['', ''],
			[false, (float)0],
			[0, (float)0],
			['0', (float)0],
			[1, (float)1],
			['1', (float)1],
			['one', (float)0],
			['1.1', (float)1.1],
			['1.11111', (float)1.11111],
			[1.11111, (float)1.11111],
			['1,1', (float)1.1],
		];
	}

	#[DataProvider('valueProvider')]
	public function testValue($input, $expected): void
	{
		$field = $this->field('number', [
			'value'   => $input,
			'default' => $input,
			'step'    => $input
		]);

		$this->assertSame($expected, $field->value());
		$this->assertSame($expected, $field->default());
		$this->assertSame($expected, $field->step());
	}

	public function testMin(): void
	{
		$field = $this->field('number', [
			'value' => 1,
			'min'   => 2
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax(): void
	{
		$field = $this->field('number', [
			'value' => 1,
			'max'   => 0
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testLargeValue(): void
	{
		$field = $this->field('number', [
			'value' => 1000
		]);

		$this->assertSame(1000.0, $field->value());
	}
}
