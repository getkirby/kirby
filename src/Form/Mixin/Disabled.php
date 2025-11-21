<?php

namespace Kirby\Form\Mixin;

trait Disabled
{
	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	protected bool $disabled = false;

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
