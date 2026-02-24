<?php

namespace Kirby\Form\Mixin;

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
	protected bool|null $pretty;

	public function pretty(): bool
	{
		return $this->pretty ?? false;
	}
}
