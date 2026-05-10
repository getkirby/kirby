<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;
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
	protected float|null $max;

	/**
	 * The lowest allowed number
	 */
	protected float|null $min;

	/**
	 * Allowed incremental steps between numbers (i.e `0.5`)
	 * Use `any` to allow any decimal value.
	 */
	protected float|string|null $step;

	protected float|null $value = null;

	public function __construct(
		array|string|null $after = null,
		bool|null $autofocus = null,
		array|string|null $before = null,
		float|string|null $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		string|null $icon = null,
		array|string|null $label = null,
		array|string|null $placeholder = null,
		float|null $max = null,
		float|null $min = null,
		string|null $name = null,
		bool|null $required = null,
		float|string|null $step = null,
		bool|null $translate = null,
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
			width:     $width,
		);

		$this->after       = $after;
		$this->before      = $before;
		$this->icon        = $icon;
		$this->max         = $max;
		$this->min         = $min;
		$this->placeholder = $placeholder;
		$this->step        = $step;
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
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

	public function props(): array
	{
		return [
			...parent::props(),
			'after'       => $this->after(),
			'before'      => $this->before(),
			'icon'        => $this->icon(),
			'max'         => $this->max(),
			'min'         => $this->min(),
			'placeholder' => $this->placeholder(),
			'step'        => $this->step(),
		];
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
		return match(true) {
			$value === ''     => null,
			is_null($value)   => $value,
			is_bool($value)   => $value,
			is_float($value)  => $value,
			is_int($value)    => $value,
			default           => (float)Str::float($value),
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
