<?php

namespace Kirby\Form\Mixin;

trait Maxlength
{
	/**
	 * Sets the maximum length of the field
	 */
	protected int|null $maxlength;

	public function maxlength(): int|null
	{
		return $this->maxlength;
	}

	protected function setMaxlength(int|null $maxlength = null): void
	{
		$this->maxlength = $maxlength;
	}
}
