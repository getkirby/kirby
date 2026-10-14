<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `when` prop for conditional field visibility
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait When
{
	/**
	 * Conditions when the field will be shown
	 *
	 * @since 3.1.0
	 */
	#[Prop]
	protected array|null $when = null;

	/**
	 * Checks if the field is currently active
	 * or hidden because of a `when` condition
	 */
	public function isActive(): bool
	{
		$when = $this->when();

		if ($when === null || $when === []) {
			return true;
		}

		$siblings = $this->siblings();

		foreach ($when as $field => $value) {
			$field = $siblings->get($field);
			$input = '';

			if ($field?->hasValue() === true) {
				$input = $field->value() ?? '';
			}

			// if the input data doesn't match the requested `when` value,
			// that means that this field is not required and can be saved
			// (*all* `when` conditions must be met for this field to be required)
			if ($input !== $value) {
				return false;
			}
		}

		return true;
	}

	public function when(): array|null
	{
		return $this->when;
	}
}
