<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Derived;

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

	protected string|null $format = self::ISO;

	/**
	 * Custom format (dayjs tokens: `HH`, `hh`, `mm`, `ss`, `a`) that is
	 * used to display the field in the Panel
	 */
	#[Derived]
	protected string|null $display = null;

	protected string|null $icon = 'clock';

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
	protected int $notation = 24;

	/**
	 * Round to the nearest: sub-options for `unit` (minute) and `size` (5)
	 */
	protected array $step = ['size' => 5, 'unit' => 'minute'];

	public function __construct(
		int|null $notation = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->notation = $notation ?? $this->notation;
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
