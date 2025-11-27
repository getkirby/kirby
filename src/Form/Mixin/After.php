<?php

namespace Kirby\Form\Mixin;

trait After
{
	/**
	 * Optional text that will be shown after the input
	 */
	protected array|string|null $after;

	public function after(): string|null
	{
		return $this->stringTemplateI18n($this->after);
	}
}
