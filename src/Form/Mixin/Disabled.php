<?php

namespace Kirby\Form\Mixin;

trait Disabled
{
	protected bool $disabled = false;

	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	public function disabled(): bool
	{
		return $this->disabled;
	}

	public function isDisabled(): bool
	{
		return $this->disabled;
	}

	protected function setDisabled(bool $disabled = false): void
	{
		$this->disabled = $disabled;
	}
}
