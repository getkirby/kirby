<?php

namespace Kirby\Form\Mixin;

trait Font
{
	/**
	 * Sets the font family (sans or monospace)
	 */
	protected string|null $font;

	public function font(): string|null
	{
		return $this->font === 'monospace' ? 'monospace' : 'sans-serif';
	}

	protected function setFont(string|null $font): void
	{
		$this->font = $font;
	}
}
