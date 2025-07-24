<?php

namespace Kirby\Form\Mixin;

trait Font
{
	/**
	 * The font used for the field.
	 */
	protected string $font;

	public function font(): string
	{
		return $this->font;
	}

	protected function setFont(string|null $font = null): void
	{
		$this->font = $font === 'monospace' ? 'monospace' : 'sans-serif';
	}
}
