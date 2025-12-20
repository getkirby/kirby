<?php

namespace Kirby\Form\Mixin;

trait Columns
{
	/**
	 * Arranges the inputs in the given number of columns
	 */
	protected int|null $columns;

	public function columns(): int
	{
		return $this->columns ?? 1;
	}
}
