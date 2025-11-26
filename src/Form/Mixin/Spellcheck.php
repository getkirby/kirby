<?php

namespace Kirby\Form\Mixin;

trait Spellcheck
{
	/**
	 * If `false`, spellcheck will be switched off
	 */
	protected bool|null $spellcheck;

	public function spellcheck(): bool
	{
		return $this->spellcheck ?? true;
	}
}
