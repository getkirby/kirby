<?php

namespace Kirby\Form\Mixin;

trait SortBy
{
	/**
	 * Sorts the entries by the given field and order (i.e. `title desc`)
	 * Drag & drop is disabled in this case
	 */
	protected string|null $sortBy;

	public function sortBy(): string|null
	{
		return $this->sortBy;
	}
}
