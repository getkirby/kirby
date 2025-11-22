<?php

namespace Kirby\Form\Mixin;

trait Autofocus
{
	/**
	 * Sets the focus on this field when the form loads. Only the first field with this label gets
	 */
	protected bool $autofocus = false;

	public function autofocus(): bool
	{
		return $this->autofocus;
	}

	protected function setAutofocus(bool $autofocus): void
	{
		$this->autofocus = $autofocus;
	}
}
