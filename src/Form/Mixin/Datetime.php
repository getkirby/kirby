<?php

namespace Kirby\Form\Mixin;

use Kirby\Toolkit\Date;

/**
 * Datetime field functionality
 *
 * @since 6.0.0
 */
trait Datetime
{
	/**
	 * Defines a custom format used when the field is saved
	 */
	protected string|null $format;

	/**
	 * Defines a step for the datetime field
	 * The step can be an array with 'unit' and 'size' keys,
	 * or a string or integer representing the step size.
	 */
	protected array|string|int|null $step;

	public function format(): string|null
	{
		return $this->format;
	}

	protected function setFormat(string|null $format = null): void
	{
		$this->format = $format;
	}

	public function toDatetime(mixed $value, string $format = 'Y-m-d H:i:s'): string|null
	{
		if ($date = Date::optional($value)) {
			if ($this->step) {
				$step = Date::stepConfig($this->step);
				$date->round($step['unit'], $step['size']);
			}

			return $date->format($format);
		}

		return null;
	}

	public function save(mixed $value): string
	{
		if ($date = Date::optional($value)) {
			return $date->format($this->format);
		}

		return '';
	}

	public function toStoredValue(): string
	{
		$value = parent::toStoredValue();

		if ($date = Date::optional($value)) {
			return $date->format($this->format);
		}

		return '';
	}
}
