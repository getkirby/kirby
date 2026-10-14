<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `counter` prop to show or hide a character counter
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Counter
{
	/**
	 * Shows or hides the character counter in the top right corner
	 */
	#[Prop]
	protected bool $counter = true;

	public function counter(): bool
	{
		return $this->counter;
	}
}
