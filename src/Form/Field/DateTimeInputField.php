<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Mixin;
use Kirby\Toolkit\Date;

/**
 * Input class for fields that have a datetime value
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class DateTimeInputField extends InputField
{
	use Mixin\Icon;

	public const ISO = 'Y-m-d H:i:s';

	protected string|null $display;

	/**
	 * Defines a custom format that is used when the field is saved
	 */
	protected string|null $format;

	protected string|null $max;
	protected string|null $min;
	protected array|int|string|null $step;

	/**
	 * @var \Kirby\Toolkit\Date|null
	 */
	protected mixed $value = null;

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
		bool|null $required = null,
		bool|null $translate = null,
		array|int|string|null $step = null,
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

		$this->display = $display;
		$this->format  = $format;
		$this->icon    = $icon;
		$this->max     = $max;
		$this->min     = $min;
		$this->step    = $step;
	}

	public function default(): string|null
	{
		$default = Date::optional(parent::default());
		$default = $this->roundToStep($default);
		return $default?->format(static::ISO);
	}

	abstract public function display(): string;

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
	public function fill(mixed $value): static
	{
		$value = Date::optional($value);
		$value = $this->roundToStep($value);
		return parent::fill($value);
	}

	abstract public function format(): string;

	abstract public function icon(): string;

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
			'display' => $this->display(),
			'format'  => $this->format(),
			'icon'    => $this->icon(),
			'max'     => $this->max(),
			'min'     => $this->min(),
			'step'    => $this->step(),
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
				data: ['date' => $min->format($format)]
			);
		}

		if ($max && $value->isMax($max) === false) {
			throw new InvalidArgumentException(
				key: 'validation.' . $this->type() . '.before',
				data: ['date' => $max->format($format)]
			);
		}
	}
}
