<?php

namespace Kirby\Form\Mixin;

trait Autocomplete
{
	/**
	 * Sets the HTML5 autocomplete attribute
	 */
	protected string|null $autocomplete;

	public function autocomplete(): string|null
	{
		return $this->autocomplete;
	}
}
