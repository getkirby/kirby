<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `spellcheck` prop to control browser spellcheck
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Spellcheck
{
	/**
	 * If `false`, spellcheck will be switched off
	 */
	protected bool|null $spellcheck;

	public function spellcheck(): bool|null
	{
		return $this->spellcheck;
	}
}
