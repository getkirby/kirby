<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\Str;

/**
 * Number field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class NumberField extends InputField
{
	use Mixin\After;
	use Mixin\Before;
	use Mixin\Icon;
	use Mixin\Placeholder;

	/**
	 * The highest allowed number
	 */
	#[Prop]
	protected float|null $max = null;

	/**
	 * The lowest allowed number
	 */
	#[Prop]
	protected float|null $min = null;

	/**
	 * Allowed incremental steps between numbers (i.e `0.5`)
	 * Use `any` to allow any decimal value.
	 */
	#[Prop]
	protected float|string|null $step = null;

	protected float|null $value = null;

	public function fill(mixed $value): static
	{
		return parent::fill(
			value: static::toNumber($value)
		);
	}

	public function default(): float|null
	{
		return static::toNumber(
			value: parent::default()
		);
	}

	public function max(): float|null
	{
		return $this->max;
	}

	public function min(): float|null
	{
		return $this->min;
	}

	public function step(): float|string|null
	{
		return match ($this->step) {
			'any'   => 'any',
			default => static::toNumber($this->step)
		};
	}

	public static function toNumber(mixed $value): float|null
	{
		if (is_string($value) === true) {
			$value = Str::float($value);
		}

		return match(true) {
			is_null($value)  => null,
			is_float($value) => $value,
			default          => (float)$value,
		};
	}

	protected function validations(): array
	{
		return [
			'min',
			'max'
		];
	}
}
