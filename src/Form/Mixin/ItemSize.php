<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `size` prop to set the card size for `layout: cards`
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait ItemSize
{
	/**
	 * Size for `layout: cards`. Available sizes:
	 * `auto`, `tiny`, `small`, `medium`, `large`, `huge`, `full`
	 */
	protected string $size = 'auto';

	public function size(): string
	{
		return $this->size;
	}
}
