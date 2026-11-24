<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

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
	#[Prop]
	protected string|null $icon = null;

	public function icon(): string|null
	{
		return $this->icon;
	}
}
