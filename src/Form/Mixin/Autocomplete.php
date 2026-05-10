<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `autocomplete` prop for setting HTML5 autocomplete behavior
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Autocomplete
{
	/**
	 * Sets the HTML5 autocomplete mode for the input
	 */
	protected string|null $autocomplete;

	public function autocomplete(): string|null
	{
		return $this->autocomplete;
	}
}
