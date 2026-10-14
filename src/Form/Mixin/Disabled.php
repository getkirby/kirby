<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `disabled` prop to make the field non-editable and prevent saving
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Disabled
{
	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	#[Prop(getter: 'isDisabled')]
	protected bool $disabled = false;

	public function disabled(): bool
	{
		return $this->disabled;
	}

	public function isDisabled(): bool
	{
		return $this->disabled();
	}
}
