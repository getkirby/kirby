<?php

namespace Kirby\Form\Mixin;

trait Min
{
	/**
	 * Sets the minimum number of required items in the field
	 */
	protected int|null $min;

	public function min(): int|null
	{
		// set min to at least 1, if required
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
