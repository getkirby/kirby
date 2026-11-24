<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `theme` prop to control the visual design variant
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Theme
{
	/**
	 * The design theme for the field
	 */
	#[Prop]
	protected string|null $theme = null;

	public function theme(): string|null
	{
		return $this->theme;
	}
}
