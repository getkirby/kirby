<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Mixin;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\Date;

/**
 * Input class for fields that have a datetime value
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class DateTimeField extends InputField
{
	use Mixin\Icon;

	public const ISO = 'Y-m-d H:i:s';

	#[Prop]
	protected string|null $display = null;

	/**
	 * Defines a custom format that is used when the field is saved
	 */
	#[Prop]
	protected string|null $format = null;

	#[Prop]
	protected string|null $max = null;
	#[Prop]
	protected string|null $min = null;
	#[Prop(setter: 'setStep')]
	protected array $step = [];

	protected Date|null $value = null;

	public function default(): string|null
	{
		$default = Date::optional(parent::default());
		$default = $this->roundToStep($default);
		return $default?->format(static::ISO);
	}

	abstract public function display(): string;

	public function fill(mixed $value): static
	{
		$value = Date::optional($value);
		$value = $this->roundToStep($value);
		return parent::fill($value);
	}

	abstract public function format(): string;

	public function max(): string|null
	{
		return Date::optional($this->max)?->format(static::ISO);
	}

	public function min(): string|null
	{
		return Date::optional($this->min)?->format(static::ISO);
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

	/**
	 * Normalizes the step option, which can be
	 * passed as int, string or array
	 */
	protected function setStep(array|int|string $step): void
	{
		$this->step = Date::stepConfig($step, $this->step);
	}

	abstract public function step(): array;

	public function toFormValue(): string
	{
		return $this->value?->format(static::ISO) ?? '';
	}

	public function toStoredValue(): string
	{
		return $this->value?->format($this->format()) ?? '';
	}

	protected function validations(): array
	{
		return [
			'minMax' => fn ($value) => $this->validateMinMax($value)
		];
	}

	protected function validateMinMax(mixed $value): void
	{
		if (!$value = Date::optional($value)) {
			return;
		}

		$min    = Date::optional($this->min());
		$max    = Date::optional($this->max());
		$format = $this->format();

		if ($min && $max && $value->isBetween($min, $max) === false) {
			throw new InvalidArgumentException(
				key: 'validation.' . $this->type() . '.between',
				data: [
					'min' => $min->format($format),
					'max' => $max->format($format)
				]
			);
		}

		if ($min && $value->isMin($min) === false) {
			throw new InvalidArgumentException(
				key: 'validation.' . $this->type() . '.after',
				data: [$this->type() => $min->format($format)]
			);
		}

		if ($max && $value->isMax($max) === false) {
			throw new InvalidArgumentException(
				key: 'validation.' . $this->type() . '.before',
				data: [$this->type() => $max->format($format)]
			);
		}
	}
}
