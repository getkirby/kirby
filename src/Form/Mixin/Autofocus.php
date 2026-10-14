<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

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
	#[Prop]
	protected bool $autofocus = false;

	public function autofocus(): bool
	{
		return $this->autofocus;
	}
}
