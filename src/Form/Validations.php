<?php

namespace Kirby\Form;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\V;

/**
 * Often used validation rules for fields
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Validations
{
	/**
	 * Validates if the field value is boolean
	 *
	 * @param \Kirby\Form\Field|\Kirby\Form\FieldClass $field
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function boolean($field, $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function date(Field|FieldClass $field, mixed $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function email(Field|FieldClass $field, mixed $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function max(Field|FieldClass $field, mixed $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function maxlength(Field|FieldClass $field, mixed $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function min(Field|FieldClass $field, mixed $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function minlength(Field|FieldClass $field, mixed $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function pattern(Field|FieldClass $field, mixed $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function required(Field|FieldClass $field, mixed $value): bool
	{
		if (
			$field->isRequired() === true &&
			$field->isSaveable() === true &&
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function option(Field|FieldClass $field, mixed $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function options(Field|FieldClass $field, mixed $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function time(Field|FieldClass $field, mixed $value): bool
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
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function url(Field|FieldClass $field, mixed $value): bool
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
