<?php

namespace Kirby\Form\Field;

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
class DateField extends DateTimeInputField
{
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
	protected array|int|string|null $step;

	/**
	 * Pass `true` or an array of time field options to show the time selector.
	 */
	protected bool|array|null $time;

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
		array|int|string|null $step = null,
		bool|array|null $time = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default:   $default,
			display:   $display,
			disabled:  $disabled,
			format:    $format,
			help:      $help,
			icon:      $icon,
			label:     $label,
			max:       $max,
			min:       $min,
			name:      $name,
			required:  $required,
			step:      $step,
			translate: $translate,
			when:      $when,
			width:     $width
		);

		$this->calendar = $calendar;
		$this->time     = $time;
	}

	public function calendar(): bool
	{
		return $this->calendar ?? true;
	}

	public function display(): string
	{
		return Str::upper($this->i18n($this->display) ?? 'YYYY-MM-DD');
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

	public function props(): array
	{
		return [
			...parent::props(),
			'calendar' => $this->calendar(),
			'time'     => $this->time()
		];
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

		$props = [
			...is_array($this->time) ? $this->time : [],
			'model' => $this->model()
		];

		return TimeField::factory($props)->toArray();
	}

	protected function validations(): array
	{
		return [
			'date',
			...parent::validations()
		];
	}
}
