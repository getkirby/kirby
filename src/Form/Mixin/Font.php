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
	protected string|null $font;

	public function font(): string|null
	{
		return match ($this->font) {
			'monospace', 'mono' => 'monospace',
			default             => 'sans-serif'
		};
	}
}
