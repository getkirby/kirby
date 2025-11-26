<?php

namespace Kirby\Form\Mixin;

trait Limit
{
	/**
	 * The number of entries that will be displayed on a single page. Afterwards pagination kicks in.
	 */
	protected int|null $limit;

	public function limit(): int|null
	{
		return $this->limit;
	}
}
