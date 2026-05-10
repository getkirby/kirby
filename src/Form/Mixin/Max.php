<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `max` prop for the maximum number of allowed items
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Max
{
	/**
	 * Sets the maximum number of allowed items in the field
	 */
	protected int|null $max;

	public function max(): int|null
	{
		return $this->max;
	}
}
