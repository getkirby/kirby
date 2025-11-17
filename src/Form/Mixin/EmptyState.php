<?php

namespace Kirby\Form\Mixin;

trait EmptyState
{
	/**
	 * Sets the text for the empty state box
	 */
	protected array|string|null $empty;

	protected function setEmpty(string|array|null $empty = null): void
	{
		$this->empty = $empty;
	}

	public function empty(): string|null
	{
		return $this->stringTemplate($this->i18n($this->empty));
	}
}
