<?php

namespace Kirby\Form\Mixin;

trait Required
{
	/**
	 * If `true`, the field has to be filled in correctly to be saved.
	 */
	protected bool|null $required;

	/**
	 * Checks if the field is required
	 */
	public function isRequired(): bool
	{
		return $this->required();
	}

	public function required(): bool
	{
		return $this->required ?? false;
	}
}
