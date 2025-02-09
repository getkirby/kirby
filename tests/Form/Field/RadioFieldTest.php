<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\DataProvider;

class RadioFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('radio');

		$this->assertSame('radio', $field->type());
		$this->assertSame('radio', $field->name());
		$this->assertSame('', $field->value());
		$this->assertNull($field->icon());
		$this->assertSame([], $field->options());
		$this->assertTrue($field->save());
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
	public function testValue(string $input, string $expected)
	{
		$field = $this->field('radio', [
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
