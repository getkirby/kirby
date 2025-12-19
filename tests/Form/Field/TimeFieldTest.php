<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(TimeField::class)]
#[CoversClass(DateTimeField::class)]
class TimeFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('time');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'disabled'    => false,
			'display'     => 'HH:mm',
			'format'      => 'H:i:s',
			'help'        => null,
			'hidden'      => false,
			'icon'        => 'clock',
			'label'       => 'Time',
			'max'         => null,
			'min'         => null,
			'name'        => 'time',
			'notation'    => 24,
			'required'    => false,
			'saveable'    => true,
			'step'        => ['size' => 5, 'unit' => 'minute'],
			'translate'   => true,
			'type'        => 'time',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testDisplayFor12HourNotation(): void
	{
		$field = $this->field('time', [
			'notation' => 12
		]);

		$this->assertSame('hh:mm a', $field->display());
	}

	public function testDisplayWithCustomSetup(): void
	{
		$field = $this->field('time', [
			'display' => 'HH:mm:ss'
		]);

		$this->assertSame('HH:mm:ss', $field->display());
	}

	public function testMinMax(): void
	{
		// no value
		$field = $this->field('time', [
			'value' => null
		]);

		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// no limits
		$field = $this->field('time', [
			'value' => '10:00:00'
		]);

		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// valid
		$field = $this->field('time', [
			'min'   => '09:00',
			'value' => '10:00:00',
			'max'   => '11:00'
		]);

		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// same time valid
		$field = $this->field('time', [
			'min'   => '10:00',
			'value' => '10:00:00',
			'max'   => '10:00'
		]);

		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// min & max failed
		$field = $this->field('time', [
			'value' => '10:00:00',
			'min'   => '08:00',
			'max'   => '09:00'
		]);

		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());

		// min failed
		$field = $this->field('time', [
			'min'   => '11:00',
			'value' => '10:00:00'
		]);

		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());

		// max failed
		$field = $this->field('time', [
			'value' => '10:00:00',
			'max'   => '09:00'
		]);

		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());

		// valid with seconds
		$field = $this->field('time', [
			'min'   => '09:00:00',
			'value' => '10:00:00',
			'max'   => '11:00:00',
			'step'  => 'second'
		]);

		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// invalid with seconds
		$field = $this->field('time', [
			'min'   => '10:00:05',
			'value' => '10:00:00',
			'step'  => 'second'
		]);

		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());

		$field = $this->field('time', [
			'value' => '10:00:05',
			'max'   => '10:00:00',
			'step'  => 'second'
		]);

		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());
	}

	public static function valueProvider(): array
	{
		return [
			[null, ''],
			['invalid time', ''],
			['22:33:00', '22:33:00'],
			['22:32:00', '22:30:00', 5],
			['22:33:00', '22:35:00', 5],
			['22:36:00', '22:35:00', 5],
			['22:39:00', '22:45:00', 15],
			['22:35:15', '22:35:30', ['size' => 30, 'unit' => 'second']],
			['22:35:15', '23:00:00', ['size' => 1, 'unit' => 'hour']],
			['2012-12-12 22:33:00', '22:33:00']
		];
	}

	#[DataProvider('valueProvider')]
	public function testValue($input, $expected, $step = 1): void
	{
		$field = $this->field('time', [
			'step'  => $step,
			'value' => $input,
		]);

		$this->assertSame($expected, $field->value());
	}
}
