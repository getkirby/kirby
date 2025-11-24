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

	protected function setMaxlength(int|null $maxlength): void
	{
		$this->maxlength = $maxlength;
	}
}
