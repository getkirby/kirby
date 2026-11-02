<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `min` prop for the minimum number of required items
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Min
{
	/**
	 * Sets the minimum number of required items in the field
	 */
	#[Prop]
	protected int|null $min = null;

	public function min(): int|null
	{
		// required fields need at least 1 item by default
		if ($this->required === true) {
			return $this->min ?? 1;
		}

		return $this->min;
	}

	public function isRequired(): bool
	{
		// set required to true if min is set
		if ($this->min) {
			return true;
		}

		return $this->required();
	}
}
