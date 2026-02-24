<?php

namespace Kirby\Form\Mixin;

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
	protected string|null $theme;

	public function theme(): string|null
	{
		return $this->theme;
	}
}
