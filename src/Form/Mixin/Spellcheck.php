<?php

namespace Kirby\Form\Mixin;

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
