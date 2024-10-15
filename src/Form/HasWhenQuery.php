<?php

namespace Kirby\Form;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait HasWhenQuery
{
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

	/**
	 * Checks if the field needs a value before being saved;
	 * this is the case if all of the following requirements are met:
	 * - The field is saveable
	 * - The field is required
	 * - The field is currently empty
	 * - The field is not currently inactive because of a `when` rule
	 */
	protected function needsValue(): bool
	{
		if (
			$this->isSaveable() === false ||
			$this->isRequired() === false ||
			$this->isEmpty() === false ||
			$this->isActive() === false
		) {
			return false;
		}

		return true;
	}

	/**
	 * Setter for the `when` condition
	 */
	protected function setWhen(array|null $when = null): void
	{
		$this->when = $when;
	}

	/**
	 * Returns the `when` condition of the field
	 */
	public function when(): array|null
	{
		return $this->when;
	}
}
