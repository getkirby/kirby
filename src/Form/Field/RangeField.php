<?php

namespace Kirby\Form\Field;

/**
 * Range field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class RangeField extends NumberField
{
	protected array|bool|null $tooltip;

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
		array|bool|null $tooltip = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			after: $after,
			autofocus: $autofocus,
			before: $before,
			default: $default,
			disabled: $disabled,
			help: $help,
			icon: $icon,
			label: $label,
			max: $max,
			min: $min,
			name: $name,
			placeholder: $placeholder,
			required: $required,
			step: $step,
			translate: $translate,
			when: $when,
			width: $width,
		);

		$this->tooltip = $tooltip;
	}

	public function max(): float|null
	{
		return $this->max ?? 100;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'tooltip' => $this->tooltip()
		];
	}

	public function tooltip(): array|bool
	{
		if (is_array($this->tooltip) === true) {
			return [
				'after'  => $this->i18n($this->tooltip['after'] ?? null),
				'before' => $this->i18n($this->tooltip['before'] ?? null)
			];
		}

		return $this->tooltip ?? true;
	}
}
