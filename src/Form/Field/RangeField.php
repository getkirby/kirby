<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Prop;

/**
 * Range field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class RangeField extends NumberField
{
	#[Prop]
	protected float|null $max = 100;

	/**
	 * Enables/disables the tooltip and sets the `before` and `after` values
	 */
	#[Prop]
	protected array|bool $tooltip = true;

	public function tooltip(): array|bool
	{
		if (is_array($this->tooltip) === true) {
			return [
				'after'  => $this->i18n($this->tooltip['after'] ?? null),
				'before' => $this->i18n($this->tooltip['before'] ?? null)
			];
		}

		return $this->tooltip;
	}
}
