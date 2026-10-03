<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `font` prop to set the field input font family
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Font
{
	/**
	 * Sets the font family (sans or monospace)
	 */
	protected string $font = 'sans-serif';

	public function font(): string
	{
		return match ($this->font) {
			'monospace', 'mono' => 'monospace',
			default             => 'sans-serif'
		};
	}
}
