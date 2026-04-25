<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `layout` prop to switch the field's display layout
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Layout
{
	/**
	 * Switch the layout for the field
	 */
	protected string|null $layout;

	public function layout(): string|null
	{
		return $this->layout;
	}
}
