<?php

namespace Kirby\Form\Mixin;

trait Autofocus
{
	/**
	 * Sets the focus on this field when the form loads. Only the first field with this label gets
	 */
	protected bool|null $autofocus;

	public function autofocus(): bool
	{
		return $this->autofocus ?? false;
	}

	protected function setAutofocus(bool|null $autofocus): void
	{
		$this->autofocus = $autofocus;
	}
}
