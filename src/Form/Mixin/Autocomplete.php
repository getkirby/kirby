<?php

namespace Kirby\Form\Mixin;

trait Autocomplete
{
	/**
	 * Sets the HTML5 autocomplete mode for the input
	 */
	protected string|null $autocomplete = null;

	public function autocomplete(): bool
	{
		return $this->autocomplete;
	}

	protected function setAutocomplete(bool $autocomplete): void
	{
		$this->autocomplete = $autocomplete;
	}
}
