<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `limit` prop for pagination of field entries
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Limit
{
	/**
	 * The number of entries that will be displayed on a single page. Afterwards pagination kicks in.
	 */
	protected int|null $limit;

	public function limit(): int|null
	{
		return $this->limit;
	}
}
