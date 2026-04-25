<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `icon` prop for an optional icon shown alongside the input
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Icon
{
	/**
	 * Optional icon that will be shown at the end of the field
	 */
	protected string|null $icon;

	public function icon(): string|null
	{
		return $this->icon;
	}
}
