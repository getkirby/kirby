<?php

namespace Kirby\Form\Mixin;

trait Disabled
{
	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	protected bool|null $disabled;

	public function disabled(): bool
	{
		return $this->disabled ?? false;
	}

	public function isDisabled(): bool
	{
		return $this->disabled();
	}
}
