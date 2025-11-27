<?php

namespace Kirby\Form\Mixin;

trait EmptyState
{
	/**
	 * Sets the text for the empty state box
	 */
	protected array|string|null $empty;

	public function empty(): string|null
	{
		return $this->stringTemplateI18n($this->empty);
	}
}
