<?php

namespace Kirby\Form\Mixin;

trait Minlength
{
	/**
	 * Sets the minimum length of the field
	 */
	protected int|null $minlength;

	public function minlength(): int|null
	{
		return $this->minlength;
	}

	protected function setMinlength(int|null $minlength = null): void
	{
		$this->minlength = $minlength;
	}
}
