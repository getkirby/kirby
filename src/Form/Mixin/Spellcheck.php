<?php

namespace Kirby\Form\Mixin;

trait Spellcheck
{
	/**
	 * Enable or disable spellcheck for the field
	 */
	protected bool $spellcheck;

	public function spellcheck(): bool
	{
		return $this->spellcheck;
	}

	protected function setSpellcheck(bool $spellcheck = false): void
	{
		$this->spellcheck = $spellcheck;
	}
}
