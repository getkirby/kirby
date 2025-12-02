<?php

namespace Kirby\Form\Mixin;

trait Autocomplete
{
	/**
	 * Sets the HTML5 autocomplete mode for the input
	 */
	protected string|null $autocomplete;

	public function autocomplete(): string|null
	{
		return $this->autocomplete;
	}
}
