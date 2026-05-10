<?php

namespace Kirby\Form\Mixin;

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
	protected int|null $minlength;

	public function minlength(): int|null
	{
		return $this->minlength;
	}
}
