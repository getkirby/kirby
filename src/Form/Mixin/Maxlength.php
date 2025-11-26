<?php

namespace Kirby\Form\Mixin;

trait Maxlength
{
	/**
	 * Maximum number of allowed characters
	 */
	protected int|null $maxlength;

	public function maxlength(): int|null
	{
		return $this->maxlength;
	}
}
