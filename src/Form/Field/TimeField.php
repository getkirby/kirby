<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Prop;

/**
 * Time field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TimeField extends DateTimeField
{
	public const ISO = 'H:i:s';

	#[Prop]
	protected string|null $format = self::ISO;

	/**
	 * Custom format (dayjs tokens: `HH`, `hh`, `mm`, `ss`, `a`) that is
	 * used to display the field in the Panel
	 */
	#[Prop(computed: true)]
	protected string|null $display = null;

	#[Prop]
	protected string|null $icon = 'clock';

	/**
	 * Latest time, which can be selected/saved (H:i or H:i:s)
	 */
	#[Prop]
	protected string|null $max = null;

	/**
	 * Earliest time, which can be selected/saved (H:i or H:i:s)
	 */
	#[Prop]
	protected string|null $min = null;

	/**
	 * `12` or `24` hour notation. If `12`, an AM/PM selector will be shown.
	 * If `display` is defined, that option will take priority.
	 */
	#[Prop]
	protected int $notation = 24;

	/**
	 * Round to the nearest: sub-options for `unit` (minute) and `size` (5)
	 */
	#[Prop(setter: 'setStep')]
	protected array $step = ['size' => 5, 'unit' => 'minute'];

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

	public function notation(): int
	{
		return match ($this->notation) {
			12      => 12,
			default => 24
		};
	}

	public function step(): array
	{
		return $this->step;
	}

	protected function validations(): array
	{
		return [
			'time',
			...parent::validations()
		];
	}
}
