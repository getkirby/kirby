<?php

namespace Kirby\Form\Mixin;

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
	protected bool|null $duplicate;

	public function duplicate(): bool
	{
		return $this->duplicate ?? true;
	}
}
