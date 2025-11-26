<?php

namespace Kirby\Form\Mixin;

trait Prepend
{
	/**
     * If activated, new items will be added at the start
	 */
	protected bool|null $prepend;

	public function prepend(): bool
	{
		return $this->prepend ?? true;
	}

	protected function setPrepend(bool|null $prepend): void
	{
		$this->prepend = $prepend;
	}
}
