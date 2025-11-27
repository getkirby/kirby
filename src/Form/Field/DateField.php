<?php

namespace Kirby\Form\Field;

use Kirby\Exception\Exception;
use Kirby\Form\Field;
use Kirby\Form\Mixin;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\Str;

/**
 * Date field
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class DateField extends InputField
{
	use Mixin\Icon;

	/**
	 * Activate/deactivate the dropdown calendar
	 */
	protected bool|null $calendar;

	/**
	 * Custom format (dayjs tokens: `DD`, `MM`, `YYYY`) that is
	 * used to display the field in the Panel
	 */
	protected string|null $display;

	/**
	 * Defines a custom format that is used when the field is saved
	 */
	protected string|null $format;

	/**
	 * Latest date, which can be selected/saved (Y-m-d)
	 */
	protected string|null $max;

	/**
	 * Earliest date, which can be selected/saved (Y-m-d)
	 */
	protected string|null $min;

	/**
	 * Round to the nearest: sub-options for `unit` (day) and `size` (1)
	 */
	protected array|string|null $step;

	/**
	 * Pass `true` or an array of time field options to show the time selector.
	 */
	protected bool|array|null $time;

	protected mixed $value = null;

	public function __construct(
		bool|null $autofocus = null,
		bool|null $calendar = null,
		mixed $default = null,
		string|null $display = null,
		bool|null $disabled = null,
		string|null $format = null,
		array|string|null $help = null,
		string|null $icon = null,
		array|string|null $label = null,
		string|null $max = null,
		string|null $min = null,
		string|null $name = null,
		bool|null $required = null,
		bool|null $translate = null,
		array|string|null $step = null,
		bool|array|null $time = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default:   $default,
			disabled:  $disabled,
			help:      $help,
			label:     $label,
			name:      $name,
			required:  $required,
			translate: $translate,
			when:      $when,
			width:     $width
		);

		$this->calendar = $calendar;
		$this->display  = $display;
		$this->format   = $format;
		$this->icon     = $icon;
		$this->max      = $max;
		$this->min      = $min;
		$this->step     = $step;
		$this->time     = $time;
	}

	public function calendar(): bool
	{
		return $this->calendar ?? true;
	}

	public function default(): string|null
	{
		$default = Date::optional(parent::default());
		$default = $this->roundToStep($default);
		return $default?->format('Y-m-d H:i:s');
	}

	public function display(): string
	{
		return Str::upper($this->i18n($this->display) ?? 'YYYY-MM-DD');
	}

	public function fill(mixed $value): static
	{
		$value       = Date::optional($value);
		$this->value = $this->roundToStep($value);
		return $this;
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

	public function icon(): string
	{
		return $this->icon ?? 'calendar';
	}

	public function max(): string|null
	{
		return Date::optional($this->max);
	}

	public function min(): string|null
	{
		return Date::optional($this->min);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'calendar' => $this->calendar(),
			'display'  => $this->display(),
			'format'   => $this->format(),
			'icon'     => $this->icon(),
			'max'      => $this->max(),
			'min'      => $this->min(),
			'step'     => $this->step(),
			'time'     => $this->time()
		];
	}

	protected function roundToStep(Date|null $value): Date|null
	{
		if ($value === null) {
			return null;
		}

		// if `step` option is set, round to nearest step
		if ($step = $this->step()) {
			$step = Date::stepConfig($step);
			$value->round($step['unit'], $step['size']);
		}

		return $value;
	}

	public function step(): array
	{
		$time = $this->time();

		if ($time === false || empty($time['step']) === true) {
			return Date::stepConfig($this->step, [
				'size' => 1,
				'unit' => 'day'
			]);
		}

		return Date::stepConfig($time['step'], [
			'size' => 5,
			'unit' => 'minute'
		]);
	}

	public function time(): array|bool
	{
		if ($this->time === null || $this->time === false) {
			return false;
		}

		$props = is_array($this->time) ? $this->time : [];
		$props['model'] = $this->model();
		$field = new Field('time', $props);
		return $field->toArray();
	}

	public function toFormValue(): string
	{
		return $this->value?->format('Y-m-d H:i:s') ?? '';
	}

	public function toStoredValue(): string
	{
		return $this->value?->format($this->format()) ?? '';
	}

	protected function validations(): array
	{
		return [
			'date',
			'minMax' => $this->validateMinMax(...)
		];
	}

	protected function validateMinMax(mixed $value): void
	{
		if (!$value = Date::optional($value)) {
			return;
		}

		$min    = Date::optional($this->min());
		$max    = Date::optional($this->max());
		$format = $this->time() === false ? 'd.m.Y' : 'd.m.Y H:i';

		if ($min && $max && $value->isBetween($min, $max) === false) {
			throw new Exception(
				key: 'validation.date.between',
				data: [
					'min' => $min->format($format),
					'max' => $max->format($format)
				]
			);
		}

		if ($min && $value->isMin($min) === false) {
			throw new Exception(
				key: 'validation.date.after',
				data: ['date' => $min->format($format)]
			);
		}

		if ($max && $value->isMax($max) === false) {
			throw new Exception(
				key: 'validation.date.before',
				data: ['date' => $max->format($format)]
			);
		}
	}
}
