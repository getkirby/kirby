<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `autofocus` prop to focus this field when the form loads
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
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
}
