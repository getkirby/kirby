<?php

namespace Kirby\Form\Mixin;

trait Before
{
	/**
	 * Optional text that will be shown before the input
	 */
	protected array|string|null $before = null;

	public function before(): string|null
	{
		return $this->stringTemplate($this->i18n($this->before));
	}

	protected function setBefore(array|string|null $before): void
	{
		$this->before = $before;
	}
}
