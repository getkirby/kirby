<?php

namespace Kirby\Form\Mixin;

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
	protected int|null $columns;

	public function columns(): int
	{
		return $this->columns ?? 1;
	}
}
