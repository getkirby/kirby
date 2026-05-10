<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `width` prop to set the field's column span in the field grid
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Width
{
	/**
	 * The width of the field in the field grid.
	 * Available widths: `1/1`, `1/2`, `1/3`, `1/4`, `2/3`, `3/4`
	 */
	protected string|null $width;

	public function width(): string
	{
		return $this->width ?? '1/1';
	}
}
