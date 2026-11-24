<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `columns` prop to arrange multiple inputs in a column grid
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Columns
{
	/**
	 * Arranges the inputs in the given number of columns
	 */
	#[Prop]
	protected int $columns = 1;

	public function columns(): int
	{
		return $this->columns;
	}
}
