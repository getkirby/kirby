<?php

namespace Kirby\Form\Mixin;

trait Font
{
	/**
	 * Sets the font family (sans or monospace)
	 */
	protected string|null $font;

	public function font(): string
	{
		return $this->font === 'monospace' ? 'monospace' : 'sans-serif';
	}
}
