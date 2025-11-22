<?php

namespace Kirby\Form\Mixin;

trait Spellcheck
{
	/**
	 * If `false`, spellcheck will be switched off
	 */
	protected bool $spellcheck = true;

	public function spellcheck(): bool
	{
		return $this->spellcheck;
	}

	protected function setSpellcheck(bool $spellcheck): void
	{
		$this->spellcheck = $spellcheck;
	}
}
