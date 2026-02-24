<?php

namespace Kirby\Form\Mixin;

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
	protected bool|null $counter;

	public function counter(): bool
	{
		return $this->counter ?? true;
	}
}
