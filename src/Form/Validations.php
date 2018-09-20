<?php

namespace Kirby\Form;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\V;

/**
 * Often used validation rules for fields
 */
class Validations
{
    public static function boolean(Field $field, $value)
    {
        if ($field->isEmpty($value) === false) {
            if (is_bool($value) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.boolean.invalid'
                ]);
            }
        }
    }

    public static function date(Field $field, $value)
    {
        if ($field->isEmpty($value) === false) {
            if (V::date($value) !== true) {
                throw new InvalidArgumentException([
                    'key' => 'form.date.invalid'
                ]);
            }
        }
    }

    public static function email(Field $field, $value)
    {
        if ($field->isEmpty($value) === false) {
            if (V::email($value) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.email.invalid'
                ]);
            }
        }
    }

    public static function max(Field $field, $value)
    {
        if ($field->isEmpty($value) === false && $field->max() !== null) {
            if (V::max($value, $field->max()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.max.invalid'
                ]);
            }
        }
    }

    public static function maxlength(Field $field, $value)
    {
        if ($field->isEmpty($value) === false && $field->maxlength() !== null) {
            if (V::maxLength($value, $field->maxlength()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.maxLength.invalid'
                ]);
            }
        }
    }

    public static function min(Field $field, $value)
    {
        if ($field->isEmpty($value) === false && $field->min() !== null) {
            if (V::min($value, $field->min()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.min.invalid'
                ]);
            }
        }
    }

    public static function minlength(Field $field, $value)
    {
        if ($field->isEmpty($value) === false && $field->minlength() !== null) {
            if (V::minLength($value, $field->minlength()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.minLength.invalid'
                ]);
            }
        }
    }

    public static function required(Field $field, $value)
    {
        if ($field->isRequired() === true && $field->save() === true && $field->isEmpty($value) === true) {
            throw new InvalidArgumentException([
                'key' => 'form.field.required'
            ]);
        }
    }

    public static function option(Field $field, $value)
    {
        if ($field->isEmpty($value) === false) {
            $values = array_column($field->options(), 'value');
            if (in_array($value, $values, true) !== true) {
                throw new InvalidArgumentException([
                    'key' => 'form.option.invalid'
                ]);
            }
        }
    }

    public static function options(Field $field, $value)
    {
        if ($field->isEmpty($value) === false) {
            $values = array_column($field->options(), 'value');
            foreach ($value as $key => $val) {
                if (in_array($val, $values, true) === false) {
                    throw new InvalidArgumentException([
                        'key' => 'form.option.invalid'
                    ]);
                }
            }
        }
    }

    public static function time(Field $field, $value)
    {
        if ($field->isEmpty($value) === false) {
            if (V::time($value) !== true) {
                throw new InvalidArgumentException([
                    'key' => 'form.time.invalid'
                ]);
            }
        }
    }

    public static function url(Field $field, $value)
    {
        if ($field->isEmpty($value) === false) {
            if (V::url($value) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.url.invalid',
                ]);
            }
        }
    }
}
