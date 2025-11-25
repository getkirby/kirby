<?php

namespace Kirby\Form\Mixin;

trait Theme
{
	/**
	 * The design theme for the field
	 */
	protected string|null $theme;

	public function theme(): string|null
	{
		return $this->theme;
	}

	protected function setTheme(string|null $theme): void
	{
		$this->theme = $theme;
	}
}
