<?php

namespace Kirby\Form\Mixin;

trait Autofocus
{
	/**
	 * Sets the focus on this field when the form loads. Only the first field with this label gets
	 */
	protected bool $autofocus;

	public function autofocus(): bool
	{
		return $this->autofocus;
	}

	protected function setAutofocus(bool $autofocus = false): void
	{
		$this->autofocus = $autofocus;
	}
}
