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
		return match ($this->font) {
			'monospace', 'mono' => 'monospace',
			default             => 'sans-serif'
		};
	}

	protected function setFont(string|null $font): void
	{
		$this->font = $font;
	}
}
