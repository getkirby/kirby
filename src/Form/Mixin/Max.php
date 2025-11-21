<?php

namespace Kirby\Form\Mixin;

trait Max
{
	/**
	 * Sets the maximum number of allowed items in the field
	 */
	protected int|null $max = null;

	public function max(): int|null
	{
		return $this->max;
	}

	protected function setMax(int|null $max): void
	{
		$this->max = $max;
	}
}
