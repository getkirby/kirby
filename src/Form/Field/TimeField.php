<?php

namespace Kirby\Form\Field;

use Kirby\Toolkit\Date;

/**
 * Time field
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TimeField extends DateTimeField
{
	public const ISO = 'H:i:s';

	/**
	 * Custom format (dayjs tokens: `HH`, `hh`, `mm`, `ss`, `a`) that is
	 * used to display the field in the Panel
	 */
	protected string|null $display;

	/**
	 * Latest time, which can be selected/saved (H:i or H:i:s)
	 */
	protected string|null $max;

	/**
	 * Earliest time, which can be selected/saved (H:i or H:i:s)
	 */
	protected string|null $min;

	/**
	 * `12` or `24` hour notation. If `12`, an AM/PM selector will be shown.
	 * If `display` is defined, that option will take priority.
	 */
	protected int|null $notation;

	/**
	 * Round to the nearest: sub-options for `unit` (minute) and `size` (5)
	 */
	protected array|int|string|null $step;

	public function __construct(
		bool|null $autofocus = null,
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
		int|null $notation = null,
		bool|null $required = null,
		array|int|string|null $step = null,
		bool|null $translate = null,
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

		$this->notation = $notation;
	}

	public function display(): string
	{
		if ($this->display) {
			return $this->i18n($this->display);
		}

		return $this->notation() === 24 ? 'HH:mm' : 'hh:mm a';
	}

	public function format(): string
	{
		return $this->format ?? static::ISO;
	}

	public function icon(): string
	{
		return $this->icon ?? 'clock';
	}

	public function notation(): int
	{
		return match ($this->notation) {
			12      => 12,
			default => 24
		};
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'notation' => $this->notation()
		];
	}

	public function step(): array
	{
		return Date::stepConfig($this->step, [
			'size' => 5,
			'unit' => 'minute',
		]);
	}

	protected function validations(): array
	{
		return [
			'time',
			...parent::validations()
		];
	}
}
