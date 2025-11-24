<?php

namespace Kirby\Form\Mixin;

trait Minlength
{
	/**
	 * Minimum number of required characters
	 */
	protected int|null $minlength;

	public function minlength(): int|null
	{
		return $this->minlength;
	}

	protected function setMinlength(int|null $minlength): void
	{
		$this->minlength = $minlength;
	}
}
