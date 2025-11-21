<?php

namespace Kirby\Form\Mixin;

trait After
{
	/**
	 * Optional text that will be shown after the input
	 */
	protected array|string|null $after = null;

	public function after(): string|null
	{
		return $this->stringTemplate($this->i18n($this->after));
	}

	protected function setAfter(array|string|null $after): void
	{
		$this->after = $after;
	}
}
