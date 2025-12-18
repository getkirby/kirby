<?php

namespace Kirby\Form\Field;

use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RangeField::class)]
class RangeFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('range');
		$props = $field->props();

		ksort($props);

		$expected = [
			'after'       => null,
			'autofocus'   => false,
			'before'      => null,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'icon'        => null,
			'label'       => 'Range',
			'max'         => 100.0,
			'min'         => null,
			'name'        => 'range',
			'placeholder' => null,
			'required'    => false,
			'saveable'    => true,
			'step'        => null,
			'tooltip'     => true,
			'translate'   => true,
			'type'        => 'range',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testMin(): void
	{
		$field = $this->field('range', [
			'value' => 1,
			'min'   => 2
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax(): void
	{
		$field = $this->field('range', [
			'value' => 1,
			'max'   => 0
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testTooltip(): void
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

	public function testTooltipTranslation(): void
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
