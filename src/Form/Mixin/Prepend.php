<?php

namespace Kirby\Form\Mixin;

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
	protected bool|null $prepend;

	public function prepend(): bool
	{
		return $this->prepend ?? false;
	}
}
