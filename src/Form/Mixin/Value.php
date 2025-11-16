<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\Language;
use ReflectionProperty;

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
	 * Default value for the field, which will be used when a page/file/user is created
	 */
	protected mixed $default = null;

	/**
	 * The value of the field
	 */
	protected mixed $value = null;

	/**
	 * @deprecated 5.0.0 Use `::toStoredValue()` instead to receive
	 * the value in the format that will be needed for content files.
	 *
	 * If you need to get the value with the default as fallback, you should use
	 * the fill method first `$field->fill($field->default())->toStoredValue()`
	 */
	public function data(bool $default = false): mixed
	{
		if ($default === true && $this->isEmpty() === true) {
			$this->fill($this->default());
		}

		return $this->toStoredValue();
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
	 * Returns the fallback value when the field should be empty
	 */
	public function emptyValue(): mixed
	{
		return (new ReflectionProperty($this, 'value'))->getDefaultValue();
	}

	/**
	 * Sets a new value for the field
	 */
	public function fill(mixed $value): static
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * Checks if the field has a value
	 */
	public function hasValue(): bool
	{
		return true;
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
	 * Checks if the field can be stored in the given language.
	 */
	public function isStorable(Language $language): bool
	{
		// the field cannot be stored at all if it has no value
		if ($this->hasValue() === false) {
			return false;
		}

		// the field cannot be translated into the given language
		if ($this->isTranslatable($language) === false) {
			return false;
		}

		// We don't need to check if the field is disabled.
		// A disabled field can still have a value and that value
		// should still be stored. But that value must not be changed
		// on submit. That's why we check for the disabled state
		// in the isSubmittable method.

		return true;
	}

	/**
	 * A field might have a value, but can still not be submitted
	 * because it is disabled, not translatable into the given
	 * language or not active due to a `when` rule.
	 */
	public function isSubmittable(Language $language): bool
	{
		if ($this->hasValue() === false) {
			return false;
		}

		if ($this->isTranslatable($language) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the field needs a value before being saved;
	 * this is the case if all of the following requirements are met:
	 * - The field has a value
	 * - The field is required
	 * - The field is currently empty
	 * - The field is not currently inactive because of a `when` rule
	 */
	protected function needsValue(): bool
	{
		if (
			$this->hasValue() === false ||
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
	 * @deprecated 5.0.0 Use `::hasValue()` instead
	 */
	public function save(): bool
	{
		return $this->hasValue();
	}

	protected function setDefault(mixed $default = null): void
	{
		$this->default = $default;
	}

	/**
	 * Submits a new value for the field.
	 * Fields can overwrite this method to provide custom
	 * submit logic. This is useful if the field component
	 * sends data that needs to be processed before being
	 * stored.
	 *
	 * @since 5.0.0
	 */
	public function submit(mixed $value): static
	{
		return $this->fill($value);
	}

	/**
	 * Returns the value of the field in a format to be used in forms
	 * (e.g. used as data for Panel Vue components)
	 */
	public function toFormValue(): mixed
	{
		if ($this->hasValue() === false) {
			return null;
		}

		return $this->value;
	}

	/**
	 * Returns the value of the field in a format
	 * to be stored by our storage classes
	 */
	public function toStoredValue(): mixed
	{
		return $this->toFormValue();
	}

	/**
	 * Returns the value of the field if it has a value
	 * otherwise it returns null
	 *
	 * @see `self::toFormValue()`
	 * @todo might get deprecated or reused later. Use `self::toFormValue()` instead.
	 *
	 * If you need the form value with the default as fallback, you should use
	 * the fill method first `$field->fill($field->default())->toFormValue()`
	 */
	public function value(bool $default = false): mixed
	{
		if ($default === true && $this->isEmpty() === true) {
			$this->fill($this->default());
		}

		return $this->toFormValue();
	}
}
