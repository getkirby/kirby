<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `maxlength` prop for the maximum number of allowed characters
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Maxlength
{
	/**
	 * Maximum number of allowed characters
	 */
	#[Prop]
	protected int|null $maxlength = null;

	public function maxlength(): int|null
	{
		return $this->maxlength;
	}
}
