<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\Language;
use Kirby\Toolkit\BlockCollectionAccess;
use ReflectionProperty;

/**
 * Provides value storage, filling, and retrieval for form fields.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * The using class must declare a `protected $value` property
 * (typed and with a default value that defines the "empty" value
 * used by `Value::emptyValue()` via reflection).
 *
 * @property mixed $value
 */
trait Value
{
	/**
	 * @deprecated 5.0.0 Use `::toStoredValue()` instead to receive
	 * the value in the format that will be needed for content files.
	 *
	 * If you need to get the value with the default as fallback,
	 * you should use the fill method first
	 * `$field->fill($field->default())->toStoredValue()`
	 */
	public function data(bool $default = false): mixed
	{
		if ($default === true && $this->isEmpty() === true) {
			$this->fill($this->default());
		}

		return $this->toStoredValue();
	}

	abstract public function default(): mixed;

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
	#[BlockCollectionAccess]
	public function fill(mixed $value): static
	{
		/** @psalm-suppress UndefinedThisPropertyAssignment using classes declare `$value` */
		$this->value = $value ?? $this->emptyValue();
		return $this;
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
	 * Checks if the field is saveable
	 * @deprecated 5.0.0 Use `::hasValue()` instead
	 */
	public function save(): bool
	{
		return $this->hasValue();
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
	#[BlockCollectionAccess]
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

		/** @psalm-suppress UndefinedThisPropertyFetch using classes declare `$value` */
		return $this->value ?? null;
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
	#[BlockCollectionAccess]
	public function value(bool $default = false): mixed
	{
		if ($default === true && $this->isEmpty() === true) {
			$this->fill($this->default());
		}

		return $this->toFormValue();
	}
}
