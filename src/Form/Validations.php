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
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Validations
{
    /**
     * Validates if the field value is boolean
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function boolean(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false) {
            if (is_bool($value) === false) {
                throw new InvalidArgumentException([
                    'key' => 'validation.boolean'
                ]);
            }
        }

        return true;
    }

    /**
     * Validates if the field value is valid date
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function date(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false) {
            if (V::date($value) !== true) {
                throw new InvalidArgumentException(
                    V::message('date', $value)
                );
            }
        }

        return true;
    }

    /**
     * Validates if the field value is valid email
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function email(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false) {
            if (V::email($value) === false) {
                throw new InvalidArgumentException(
                    V::message('email', $value)
                );
            }
        }

        return true;
    }

    /**
     * Validates if the field value is maximum
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function max(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false && $field->max() !== null) {
            if (V::max($value, $field->max()) === false) {
                throw new InvalidArgumentException(
                    V::message('max', $value, $field->max())
                );
            }
        }

        return true;
    }

    /**
     * Validates if the field value is max length
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function maxlength(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false && $field->maxlength() !== null) {
            if (V::maxLength($value, $field->maxlength()) === false) {
                throw new InvalidArgumentException(
                    V::message('maxlength', $value, $field->maxlength())
                );
            }
        }

        return true;
    }

    /**
     * Validates if the field value is minimum
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function min(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false && $field->min() !== null) {
            if (V::min($value, $field->min()) === false) {
                throw new InvalidArgumentException(
                    V::message('min', $value, $field->min())
                );
            }
        }

        return true;
    }

    /**
     * Validates if the field value is min length
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function minlength(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false && $field->minlength() !== null) {
            if (V::minLength($value, $field->minlength()) === false) {
                throw new InvalidArgumentException(
                    V::message('minlength', $value, $field->minlength())
                );
            }
        }

        return true;
    }

    /**
     * Validates if the field value matches defined pattern
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function pattern(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false && $field->pattern() !== null) {
            if (V::match($value, '/' . $field->pattern() . '/i') === false) {
                throw new InvalidArgumentException(
                    V::message('match')
                );
            }
        }

        return true;
    }

    /**
     * Validates if the field value is required
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function required(Field $field, $value): bool
    {
        if ($field->isRequired() === true && $field->save() === true && $field->isEmpty($value) === true) {
            throw new InvalidArgumentException([
                'key' => 'validation.required'
            ]);
        }

        return true;
    }

    /**
     * Validates if the field value is in defined options
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function option(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false) {
            $values = array_column($field->options(), 'value');

            if (in_array($value, $values, true) !== true) {
                throw new InvalidArgumentException([
                    'key' => 'validation.option'
                ]);
            }
        }

        return true;
    }

    /**
     * Validates if the field values is in defined options
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function options(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false) {
            $values = array_column($field->options(), 'value');
            foreach ($value as $val) {
                if (in_array($val, $values, true) === false) {
                    throw new InvalidArgumentException([
                        'key' => 'validation.option'
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * Validates if the field value is valid time
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function time(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false) {
            if (V::time($value) !== true) {
                throw new InvalidArgumentException(
                    V::message('time', $value)
                );
            }
        }

        return true;
    }

    /**
     * Validates if the field value is valid url
     *
     * @param \Kirby\Form\Field $field
     * @param $value
     * @return bool
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function url(Field $field, $value): bool
    {
        if ($field->isEmpty($value) === false) {
            if (V::url($value) === false) {
                throw new InvalidArgumentException(
                    V::message('url', $value)
                );
            }
        }

        return true;
    }
}
