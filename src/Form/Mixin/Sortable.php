<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `sortable` prop to enable drag & drop sorting
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Sortable
{
	/**
	 * If `true`, entries are sortable via drag & drop
	 */
	#[Prop]
	protected bool $sortable = true;

	public function sortable(): bool
	{
		return $this->sortable;
	}
}
