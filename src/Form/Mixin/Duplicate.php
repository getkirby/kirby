<?php

namespace Kirby\Form\Mixin;

trait Duplicate
{
	/**
	 * Allow duplicating items in the field
	 */
	protected bool|null $duplicate;

	public function duplicate(): bool
	{
		return $this->duplicate ?? true;
	}
}
