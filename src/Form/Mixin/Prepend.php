<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `prepend` prop to add new items at the beginning
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Prepend
{
	/**
	 * If activated, new items will be added at the start
	 */
	#[Prop]
	protected bool $prepend = false;

	public function prepend(): bool
	{
		return $this->prepend;
	}
}
