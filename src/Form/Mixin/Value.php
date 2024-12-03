<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\Language;
use Kirby\Exception\PermissionException;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Value
{
	protected mixed $default = null;
	protected mixed $value = null;

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

		return $this->stringTemplate($this->default);
	}

	/**
	 * Sets a new value for the field
	 */
	public function fill(mixed $value = null): void
	{
		$this->value  = $value;
		$this->errors = null;
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
	 * Checks if the field is saveable
	 */
	public function isSaveable(): bool
	{
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
	public function needsValue(): bool
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
	 * Checks if the field is saveable
	 * @deprecated 5.0.0 Use `::isSaveable()` instead
	 */
	public function save(): bool
	{
		return $this->isSaveable();
	}

	/**
	 * Setter for the default value property
	 */
	protected function setDefault(mixed $default = null): void
	{
		$this->default = $default;
	}

	/**
	 * Tries to set a new field value, but will throw an exception
	 * if the field is not submittable.
	 */
	public function submit(
		mixed $value = null,
		Language|string $language = 'default',
	): void {
		if ($this->isSaveable() === false) {
			throw new PermissionException('The "' . $this->name() . '" field cannot be saved');
		}

		if ($this->isDisabled($language) === true) {
			throw new PermissionException('The "' . $this->name() . '" field is disabled and cannot be submitted');
		}

		$this->fill($value);
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
