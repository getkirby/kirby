<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\DataProvider;

class TogglesFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('toggles');

		$this->assertSame('toggles', $field->type());
		$this->assertSame('toggles', $field->name());
		$this->assertSame('', $field->value());
		$this->assertTrue($field->grow());
		$this->assertTrue($field->labels());
		$this->assertTrue($field->reset());
		$this->assertSame([], $field->options());
		$this->assertTrue($field->save());
	}

	public function testGrow(): void
	{
		$field = $this->field('toggles', [
			'grow' => false
		]);

		$this->assertFalse($field->grow());

		$field = $this->field('toggles', [
			'grow' => true
		]);

		$this->assertTrue($field->grow());
	}

	public function testLabels(): void
	{
		$field = $this->field('toggles', [
			'labels' => false
		]);

		$this->assertFalse($field->labels());

		$field = $this->field('toggles', [
			'labels' => true
		]);

		$this->assertTrue($field->labels());
	}

	public function testReset(): void
	{
		$field = $this->field('toggles', [
			'reset' => false
		]);

		$this->assertFalse($field->reset());

		$field = $this->field('toggles', [
			'reset' => true
		]);

		$this->assertTrue($field->reset());
	}

	public static function valueInputProvider(): array
	{
		return [
			['a', 'a'],
			['b', 'b'],
			['c', 'c'],
			['d', '']
		];
	}

	#[DataProvider('valueInputProvider')]
	public function testValue($input, $expected): void
	{
		$field = $this->field('toggles', [
			'options' => [
				'a',
				'b',
				'c'
			],
			'value' => $input
		]);

		$this->assertTrue($expected === $field->value());
	}
}
