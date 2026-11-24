<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `duplicate` prop to allow or prevent duplicating field items
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Duplicate
{
	/**
	 * Allow duplicating items in the field
	 */
	#[Prop]
	protected bool $duplicate = true;

	public function duplicate(): bool
	{
		return $this->duplicate;
	}
}
