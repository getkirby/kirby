<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Prop;

/**
 * Toggles Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TogglesField extends OptionField
{
	/**
	 * Toggles will automatically span the full width of the field. With the grow option, you can disable this behaviour for a more compact layout.
	 */
	#[Prop]
	protected bool $grow = true;

	/**
	 * If `false` all labels will be hidden for icon-only toggles.
	 */
	#[Prop]
	protected bool $labels = true;

	/**
	 * A toggle can be deactivated on click. If resettable is `false` deactivating a toggle is no longer possible.
	 */
	#[Prop]
	protected bool $resettable = true;

	public function labels(): bool
	{
		return $this->labels;
	}

	public function grow(): bool
	{
		return $this->grow;
	}

	public function resettable(): bool
	{
		return $this->resettable;
	}
}
