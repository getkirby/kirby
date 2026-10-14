<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `layout` prop to switch how
 * a collection of items is displayed
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait ItemLayout
{
	/**
	 * Switch the layout of the items. Available layouts:
	 * `list`, `cardlets`, `cards` and `table`
	 */
	#[Prop]
	protected string $layout = 'list';

	public function layout(): string
	{
		return $this->layout;
	}
}
