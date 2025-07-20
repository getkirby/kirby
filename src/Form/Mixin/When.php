<?php

namespace Kirby\Form\Mixin;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait When
{
	/**
	 * Conditions when the field will be shown
	 *
	 * @since 3.1.0
	 */
	protected array|null $when = null;

	/**
	 * Checks if the field is currently active
	 * or hidden because of a `when` condition
	 */
	public function isActive(): bool
	{
		if ($this->when === null || $this->when === []) {
			return true;
		}

		$siblings = $this->siblings();

		foreach ($this->when as $field => $value) {
			$field = $siblings->get($field);
			$input = $field?->value() ?? '';

			// if the input data doesn't match the requested `when` value,
			// that means that this field is not required and can be saved
			// (*all* `when` conditions must be met for this field to be required)
			if ($input !== $value) {
				return false;
			}
		}

		return true;
	}

	protected function setWhen(array|null $when = null): void
	{
		$this->when = $when;
	}

	public function when(): array|null
	{
		return $this->when;
	}
}
