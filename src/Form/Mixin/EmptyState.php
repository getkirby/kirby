<?php

namespace Kirby\Form\Mixin;

trait EmptyState
{
	/**
	 * Sets the text for the empty state box
	 */
	protected array|string|null $empty = null;

	public function empty(): string|null
	{
		return $this->stringTemplate($this->i18n($this->empty));
	}

	protected function setEmpty(array|string|null $empty): void
	{
		$this->empty = $empty;
	}
}
