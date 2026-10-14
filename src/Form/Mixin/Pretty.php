<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `pretty` prop to save JSON with pretty printing
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Pretty
{
	/**
	 * Saves pretty printed JSON in text files
	 */
	#[Prop(export: false)]
	protected bool $pretty = false;

	public function pretty(): bool
	{
		return $this->pretty;
	}
}
