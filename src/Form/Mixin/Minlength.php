<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `minlength` prop for the minimum number of required characters
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Minlength
{
	/**
	 * Minimum number of required characters
	 */
	#[Prop]
	protected int|null $minlength = null;

	public function minlength(): int|null
	{
		return $this->minlength;
	}
}
