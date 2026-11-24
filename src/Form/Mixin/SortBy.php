<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `sortBy` prop to sort entries by field and direction
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait SortBy
{
	/**
	 * Sorts the entries by the given field and order (i.e. `title desc`)
	 * Drag & drop is disabled in this case
	 */
	#[Prop]
	protected string|null $sortBy = null;

	public function sortBy(): string|null
	{
		return $this->sortBy;
	}
}
