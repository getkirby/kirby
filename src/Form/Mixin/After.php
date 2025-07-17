<?php

namespace Kirby\Form\Mixin;

trait After
{
	/**
	 * Optional text that will be shown after the input
	 */
	protected string|null $after;

	public function after(): string|null
	{
		return $this->stringTemplate($this->after);
	}

	protected function setAfter(array|string|null $after = null): void
	{
		$this->after = $this->i18n($after);
	}
}
