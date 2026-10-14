<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\Str;

/**
 * Date field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class DateField extends DateTimeField
{
	/**
	 * Activate/deactivate the dropdown calendar
	 */
	#[Prop]
	protected bool $calendar = true;

	/**
	 * Custom format (dayjs tokens: `DD`, `MM`, `YYYY`) that is
	 * used to display the field in the Panel
	 */
	#[Prop]
	protected string|null $display = 'YYYY-MM-DD';

	/**
	 * Defines a custom format that is used when the field is saved
	 */
	#[Prop(computed: true)]
	protected string|null $format = null;

	#[Prop]
	protected string|null $icon = 'calendar';

	/**
	 * Latest date, which can be selected/saved (Y-m-d)
	 */
	#[Prop]
	protected string|null $max = null;

	/**
	 * Earliest date, which can be selected/saved (Y-m-d)
	 */
	#[Prop]
	protected string|null $min = null;

	/**
	 * Round to the nearest: sub-options for `unit` (day) and `size` (1)
	 */
	#[Prop(computed: true, setter: 'setStep')]
	protected array $step = ['size' => 1, 'unit' => 'day'];

	/**
	 * Pass `true` or an array of time field options to show the time selector.
	 */
	#[Prop]
	protected bool|array $time = false;

	/**
	 * Cuts a boundary down to the precision of the field, so that it
	 * can never carry more information than the field is able to produce.
	 */
	protected function boundary(string|null $boundary): string|null
	{
		$format = $this->time() === false ? 'Y-m-d' : static::ISO;
		return Date::optional($boundary)?->format($format);
	}

	public function calendar(): bool
	{
		return $this->calendar;
	}

	public function display(): string
	{
		$display = (string)($this->i18n($this->display) ?? $this->display);

		// a date marker is uppercase, whatever case it was written in,
		// but what the pattern escapes is printed as it is
		return preg_replace_callback(
			'!\[[^\]]*\]|[^\[]+!',
			fn (array $match): string => str_starts_with($match[0], '[')
				? $match[0]
				: Str::upper($match[0]),
			$display
		) ?? $display;
	}

	public function format(): string
	{
		if ($this->format) {
			return $this->format;
		}

		if ($this->time() === false) {
			return 'Y-m-d';
		}

		return 'Y-m-d H:i:s';
	}

	public function max(): string|null
	{
		return $this->boundary($this->max);
	}

	public function min(): string|null
	{
		return $this->boundary($this->min);
	}

	protected function roundToStep(Date|null $value): Date|null
	{
		// without a time selector, the time is cut off
		// instead of rounding the date to the nearest day
		if ($this->time() === false) {
			$value?->setTime(hour: 0, minute: 0);
		}

		return parent::roundToStep($value);
	}

	public function step(): array
	{
		$time = $this->time();

		if ($time === false || empty($time['step']) === true) {
			return $this->step;
		}

		return Date::stepConfig($time['step'], [
			'size' => 5,
			'unit' => 'minute'
		]);
	}

	public function time(): array|bool
	{
		if ($this->time === false) {
			return false;
		}

		$props = [
			...is_array($this->time) ? $this->time : [],
			'model' => $this->model()
		];

		return (new TimeField(...$props))->toArray();
	}

	protected function validations(): array
	{
		return [
			'date',
			...parent::validations()
		];
	}
}
