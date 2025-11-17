<?php

namespace Kirby\Form\Mixin;

trait Minlength
{
	/**
	 * Minimum number of allowed characters
	 */
	protected int|null $minlength;

	public function minlength(): int|null
	{
		return $this->minlength;
	}
}
