<?php

namespace Kirby\Form\Mixin;

trait Before
{
	/**
	 * Optional text that will be shown before the input
	 */
	protected array|string|null $before;

	public function before(): string|null
	{
		return $this->stringTemplateI18n($this->before);
	}
}
