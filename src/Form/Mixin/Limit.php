<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

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
	#[Prop]
	protected int $limit = 20;

	public function limit(): int
	{
		return $this->limit;
	}
}
