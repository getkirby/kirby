<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\Language;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Value
{
	/**
	 * @deprecated 5.0.0 Use `::toStoredValue()` instead
	 */
	public function data(bool $default = false): mixed
	{
		return $this->toStoredValue($default);
	}

	/**
	 * Returns the default value of the field
	 */
	public function default(): mixed
	{
		if (is_string($this->default) === false) {
			return $this->default;
		}

		return $this->model->toString($this->default);
	}

	/**
	 * Checks if the field is empty
	 */
	public function isEmpty(): bool
	{
		return $this->isEmptyValue($this->toFormValue());
	}

	/**
	 * Checks if the given value is considered empty
	 */
	public function isEmptyValue(mixed $value = null): bool
	{
		return in_array($value, [null, '', []], true);
	}

	/**
	 * Checks if the field is fillable. "Fillable" means that
	 * the field can receive a value for its initial state
	 * when the field is being rendered in the panel.
	 */
	public function isFillable(): bool
	{
		return $this->isSaveable() === true;
	}

	/**
	 * A field might be saveable, but can still not be submitted
	 * because it is disabled, not translatable into the given
	 * language or not active due to a `when` rule.
	 */
	public function isSubmittable(Language $language): bool
	{
		if ($this->isSaveable() === false) {
			return false;
		}

		if ($this->isDisabled() === true) {
			return false;
		}

		if ($this->isTranslatable($language) === false) {
			return false;
		}

		if ($this->isActive() === false) {
			return false;
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
	 * Returns the value of the field in a format to be used in forms
	 * @alias for `::value()`
	 */
	public function toFormValue(bool $default = false): mixed
	{
		if ($this->isSaveable() === false) {
			return null;
		}

		if ($default === true && $this->isEmpty() === true) {
			return $this->default();
		}

		return $this->value;
	}

	/**
	 * Returns the value of the field in a format to be stored by our storage classes
	 */
	public function toStoredValue(bool $default = false): mixed
	{
		return $this->toFormValue($default);
	}

	/**
	 * Returns the value of the field if saveable
	 * otherwise it returns null
	 *
	 * @alias for `::toFormValue()` might get deprecated or reused later
	 */
	public function value(bool $default = false): mixed
	{
		return $this->toFormValue($default);
	}
}
