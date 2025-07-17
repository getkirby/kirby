<?php

namespace Kirby\Form\Mixin;

trait Before
{
	/**
	 * Optional text that will be shown before the input
	 */
	protected string|null $before;

	public function before(): string|null
	{
		return $this->stringTemplate($this->before);
	}

	protected function setBefore(array|string|null $before = null): void
	{
		$this->before = $this->i18n($before);
	}
}
