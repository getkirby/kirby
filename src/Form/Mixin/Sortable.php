<?php

namespace Kirby\Form\Mixin;

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
	protected bool|null $sortable;

	public function sortable(): bool
	{
		return $this->sortable ?? true;
	}
}
