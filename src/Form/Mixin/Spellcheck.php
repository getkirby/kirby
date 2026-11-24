<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

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
	#[Prop]
	protected bool|null $spellcheck = null;

	public function spellcheck(): bool|null
	{
		return $this->spellcheck;
	}
}
