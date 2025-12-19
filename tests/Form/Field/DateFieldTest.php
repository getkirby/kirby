<?php

namespace Kirby\Form\Field;

use Kirby\Toolkit\Date;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(DateField::class)]
#[CoversClass(DateTimeField::class)]
class DateFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('date');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'calendar'    => true,
			'disabled'    => false,
			'display'     => 'YYYY-MM-DD',
			'format'      => 'Y-m-d',
			'help'        => null,
			'hidden'      => false,
			'icon'        => 'calendar',
			'label'       => 'Date',
			'max'         => null,
			'min'         => null,
			'name'        => 'date',
			'required'    => false,
			'saveable'    => true,
			'step'        => ['size' => 1, 'unit' => 'day'],
			'time'        => false,
			'translate'   => true,
			'type'        => 'date',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testEmptyDate(): void
	{
		$field = $this->field('date', [
			'value' => null
		]);

		$this->assertSame('', $field->value());
	}

	public function testMinMax(): void
	{
		// empty
		$field = $this->field('date', [
			'min'   => '2020-10-01',
			'max'   => '2020-10-31'
		]);

		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// no limits
		$field = $this->field('date', [
			'value' => '2020-10-10'
		]);

		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// valid
		$field = $this->field('date', [
			'min'   => '2020-10-01',
			'value' => '2020-10-10',
			'max'   => '2020-10-31'
		]);

		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// same day valid
		$field = $this->field('date', [
			'min'   => '2020-10-10',
			'value' => '2020-10-10',
			'max'   => '2020-10-10'
		]);

		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// min & max failed
		$field = $this->field('date', [
			'min'   => '2020-10-01',
			'max'   => '2020-10-02',
			'value' => '2020-10-03'
		]);

		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());
		$this->assertSame([
			'minMax' => 'Please enter a date between 2020-10-01 and 2020-10-02',
		], $field->errors());

		// min & max failed (with time)
		$field = $this->field('date', [
			'time'  => true,
			'min'   => '2020-10-01 10:04',
			'max'   => '2020-10-02 08:15',
			'value' => '2020-10-03 12:34'
		]);

		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());
		$this->assertSame([
			'minMax' => 'Please enter a date between 2020-10-01 10:04:00 and 2020-10-02 08:15:00',
		], $field->errors());

		// min failed
		$field = $this->field('date', [
			'min'   => '2020-10-01',
			'value' => '2020-09-10'
		]);

		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());
		$this->assertSame([
			'minMax' => 'Please enter a date after 2020-10-01',
		], $field->errors());

		// max failed
		$field = $this->field('date', [
			'max'   => '2020-10-31',
			'value' => '2020-11-10'
		]);

		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());
		$this->assertSame([
			'minMax' => 'Please enter a date before 2020-10-31',
		], $field->errors());
	}

	public function testReset(): void
	{
		$field = $this->field('date');
		$field->fill('2012-12-12');

		$this->assertSame('2012-12-12 00:00:00', $field->toFormValue());

		$field->reset();

		$this->assertSame('', $field->toFormValue());
	}

	public static function valueProvider(): array
	{
		return [
			['12.12.2012', date('Y-m-d H:i:s', strtotime('2012-12-12'))],
			['2016-11-21', date('Y-m-d H:i:s', strtotime('2016-11-21'))],
			['2016-11-21 12:12:12', date('Y-m-d H:i:s', strtotime('2016-11-21 12:10:00')), 5],
			['something', ''],
		];
	}

	public function testSave(): void
	{
		// default value
		$field = $this->field('date', [
			'value' => '12.12.2012',
		]);

		$this->assertSame('2012-12-12', $field->data());

		// empty value
		$field = $this->field('date', [
			'value'  => null,
		]);

		$this->assertSame('', $field->data());
	}

	/**
	 * @link https://github.com/getkirby/kirby/issues/3642
	 */
	public function testTimeWithDefaultNow(): void
	{
		$field = $this->field('date', [
			'time'    => true,
			'default' => 'now',
		]);

		$now = (new Date())->round('minute', 5)->toString(timezone: false);
		$this->assertSame($now, $field->default());
	}

	#[DataProvider('valueProvider')]
	public function testValue($input, $expected, $step = null): void
	{
		$field = $this->field('date', [
			'value' => $input,
			'time'  => ['step' => $step]
		]);

		$this->assertSame($expected, $field->value());
	}
}
