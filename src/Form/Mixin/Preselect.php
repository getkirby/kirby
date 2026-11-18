<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `preselect` prop to select the value when the form loads
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Preselect
{
	/**
	 * Focuses the input and selects its value when the form loads
	 */
	#[Prop]
	protected bool $preselect = false;

	public function preselect(): bool
	{
		return $this->preselect;
	}
}
