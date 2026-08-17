<?php

namespace Kirby\Form;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\V;

/**
 * Often used validation rules for fields
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Validations
{
	/**
	 * Validates if the field value is boolean
	 *
	 * @throws InvalidArgumentException
	 */
	public static function boolean(Field $field, mixed $value): bool
	{
		if ($field->isEmptyValue($value) === false) {
			if (is_bool($value) === false) {
				throw new InvalidArgumentException(
					key: 'validation.boolean'
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is valid date
	 *
	 * @throws InvalidArgumentException
	 */
	public static function date(Field $field, mixed $value): bool
	{
		if ($field->isEmptyValue($value) === false) {
			if (V::date($value) !== true) {
				throw new InvalidArgumentException(
					message: V::message('date', $value)
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is valid email
	 *
	 * @throws InvalidArgumentException
	 */
	public static function email(Field $field, mixed $value): bool
	{
		if ($field->isEmptyValue($value) === false) {
			if (V::email($value) === false) {
				throw new InvalidArgumentException(
					message: V::message('email', $value)
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is maximum
	 *
	 * @throws InvalidArgumentException
	 */
	public static function max(Field $field, mixed $value): bool
	{
		if (
			$field->isEmptyValue($value) === false &&
			$field->max() !== null
		) {
			if (V::max($value, $field->max()) === false) {
				throw new InvalidArgumentException(
					message: V::message('max', $value, $field->max())
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is max length
	 *
	 * @throws InvalidArgumentException
	 */
	public static function maxlength(Field $field, mixed $value): bool
	{
		if (
			$field->isEmptyValue($value) === false &&
			$field->maxlength() !== null
		) {
			if (V::maxLength($value, $field->maxlength()) === false) {
				throw new InvalidArgumentException(
					message: V::message('maxlength', $value, $field->maxlength())
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is minimum
	 *
	 * @throws InvalidArgumentException
	 */
	public static function min(Field $field, mixed $value): bool
	{
		if (
			$field->isEmptyValue($value) === false &&
			$field->min() !== null
		) {
			if (V::min($value, $field->min()) === false) {
				throw new InvalidArgumentException(
					message: V::message('min', $value, $field->min())
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is min length
	 *
	 * @throws InvalidArgumentException
	 */
	public static function minlength(Field $field, mixed $value): bool
	{
		if (
			$field->isEmptyValue($value) === false &&
			$field->minlength() !== null
		) {
			if (V::minLength($value, $field->minlength()) === false) {
				throw new InvalidArgumentException(
					message: V::message('minlength', $value, $field->minlength())
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value matches defined pattern
	 *
	 * @throws InvalidArgumentException
	 */
	public static function pattern(Field $field, mixed $value): bool
	{
		if ($field->isEmptyValue($value) === false) {
			if ($pattern = $field->pattern()) {
				// ensure that that pattern needs to match the whole
				// input value from start to end, not just a partial match
				// https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/pattern#overview
				$pattern = '^(?:' . $pattern . ')$';

				if (V::match($value, '/' . $pattern . '/i') === false) {
					throw new InvalidArgumentException(
						message: V::message('match')
					);
				}
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is required
	 *
	 * @throws InvalidArgumentException
	 */
	public static function required(Field $field, mixed $value): bool
	{
		if (
			$field->hasValue() === true &&
			$field->isRequired() === true &&
			$field->isEmptyValue($value) === true
		) {
			throw new InvalidArgumentException(
				key: 'validation.required'
			);
		}

		return true;
	}

	/**
	 * Validates if the field value is in defined options
	 *
	 * @throws InvalidArgumentException
	 */
	public static function option(Field $field, mixed $value): bool
	{
		if ($field->isEmptyValue($value) === false) {
			$values = array_column($field->options(), 'value');

			if (in_array($value, $values, true) !== true) {
				throw new InvalidArgumentException(
					key: 'validation.option'
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field values is in defined options
	 *
	 * @throws InvalidArgumentException
	 */
	public static function options(Field $field, mixed $value): bool
	{
		if ($field->isEmptyValue($value) === false) {
			$values = array_column($field->options(), 'value');
			foreach ($value as $val) {
				if (in_array($val, $values, true) === false) {
					throw new InvalidArgumentException(
						key: 'validation.option'
					);
				}
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is valid time
	 *
	 * @throws InvalidArgumentException
	 */
	public static function time(Field $field, mixed $value): bool
	{
		if ($field->isEmptyValue($value) === false) {
			if (V::time($value) !== true) {
				throw new InvalidArgumentException(
					message: V::message('time', $value)
				);
			}
		}

		return true;
	}

	/**
	 * Validates if the field value is valid url
	 *
	 * @throws InvalidArgumentException
	 */
	public static function url(Field $field, mixed $value): bool
	{
		if ($field->isEmptyValue($value) === false) {
			if (V::url($value) === false) {
				throw new InvalidArgumentException(
					message: V::message('url', $value)
				);
			}
		}

		return true;
	}
}
