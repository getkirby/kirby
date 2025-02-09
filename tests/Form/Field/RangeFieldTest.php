<?php

namespace Kirby\Form\Field;

use Kirby\Toolkit\I18n;

class RangeFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('range');

		$this->assertSame('range', $field->type());
		$this->assertSame('range', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame('', $field->default());
		$this->assertNull($field->min());
		$this->assertSame(100.0, $field->max());
		$this->assertSame('', $field->step());
		$this->assertTrue($field->tooltip());
		$this->assertTrue($field->save());
	}

	public function testMin()
	{
		$field = $this->field('range', [
			'value' => 1,
			'min'   => 2
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax()
	{
		$field = $this->field('range', [
			'value' => 1,
			'max'   => 0
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testTooltip()
	{
		$field = $this->field('range', [
			'tooltip' => [
				'before' => 'per',
				'after'  => 'months'
			]
		]);

		$tooltip = $field->tooltip();
		$this->assertIsArray($tooltip);
		$this->assertSame('per', $tooltip['before']);
		$this->assertSame('months', $tooltip['after']);
	}

	public function testTooltipTranslation()
	{
		$props = [
			'tooltip' => [
				'before' => [
					'en' => 'per',
					'de' => 'pro'
				],
				'after' => [
					'en' => 'months',
					'de' => 'monate'
				]
			]
		];

		I18n::$locale = 'en';
		$tooltip = $this->field('range', $props)->tooltip();
		$this->assertIsArray($tooltip);
		$this->assertSame('per', $tooltip['before']);
		$this->assertSame('months', $tooltip['after']);


		I18n::$locale = 'de';
		$tooltip = $this->field('range', $props)->tooltip();
		$this->assertIsArray($tooltip);
		$this->assertSame('pro', $tooltip['before']);
		$this->assertSame('monate', $tooltip['after']);
	}
}
