<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\DataProvider;

class DateFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('date');

		$this->assertSame('date', $field->type());
		$this->assertSame('date', $field->name());
		$this->assertSame('', $field->value());
		$this->assertNull($field->min());
		$this->assertNull($field->max());
		$this->assertFalse($field->time());
		$this->assertTrue($field->save());
	}

	public function testEmptyDate(): void
	{
		$field = $this->field('date', [
			'value' => null
		]);

		$this->assertSame('', $field->value());
		$this->assertNull($field->toString());
	}

	public function testMinMax(): void
	{
		// empty
		$field = $this->field('date', [
			'min'   => '2020-10-01',
			'max'   => '2020-10-31'
		]);

		$field->validate();
		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// no limits
		$field = $this->field('date', [
			'value' => '2020-10-10'
		]);

		$field->validate();
		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// valid
		$field = $this->field('date', [
			'min'   => '2020-10-01',
			'value' => '2020-10-10',
			'max'   => '2020-10-31'
		]);

		$field->validate();
		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// same day valid
		$field = $this->field('date', [
			'min'   => '2020-10-10',
			'value' => '2020-10-10',
			'max'   => '2020-10-10'
		]);

		$field->validate();
		$this->assertTrue($field->isValid());
		$this->assertFalse($field->isInvalid());

		// min & max failed
		$field = $this->field('date', [
			'min'   => '2020-10-01',
			'max'   => '2020-10-02',
			'value' => '2020-10-03'
		]);

		$field->validate();
		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());
		$this->assertSame([
			'minMax' => 'Please enter a date between 01.10.2020 and 02.10.2020',
		], $field->errors());

		// min & max failed (with time)
		$field = $this->field('date', [
			'time'  => true,
			'min'   => '2020-10-01 10:04',
			'max'   => '2020-10-02 08:15',
			'value' => '2020-10-03 12:34'
		]);

		$field->validate();
		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());
		$this->assertSame([
			'minMax' => 'Please enter a date between 01.10.2020 10:04 and 02.10.2020 08:15',
		], $field->errors());

		// min failed
		$field = $this->field('date', [
			'min'   => '2020-10-01',
			'value' => '2020-09-10'
		]);

		$field->validate();
		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());
		$this->assertSame([
			'minMax' => 'Please enter a date after 01.10.2020',
		], $field->errors());

		// max failed
		$field = $this->field('date', [
			'max'   => '2020-10-31',
			'value' => '2020-11-10'
		]);

		$field->validate();
		$this->assertFalse($field->isValid());
		$this->assertTrue($field->isInvalid());
		$this->assertSame([
			'minMax' => 'Please enter a date before 31.10.2020',
		], $field->errors());
	}

	public function testFillWithEmptyValue(): void
	{
		$field = $this->field('date');
		$field->fill('2012-12-12');

		$this->assertSame('2012-12-12 00:00:00', $field->toFormValue());

		$field->fillWithEmptyValue();

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

		$now = date('Y-m-d H:i:s', strtotime('now'));
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
