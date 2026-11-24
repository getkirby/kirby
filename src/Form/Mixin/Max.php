<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

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
	#[Prop]
	protected int|null $max = null;

	public function max(): int|null
	{
		return $this->max;
	}
}
