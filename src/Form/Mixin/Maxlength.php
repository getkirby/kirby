<?php

namespace Kirby\Form\Mixin;

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
	protected int|null $maxlength;

	public function maxlength(): int|null
	{
		return $this->maxlength;
	}
}
