<?php

namespace Kirby\Form;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\V;

class Validate
{

    public static function boolean(Field $field)
    {
        if ($field->isEmpty() === false) {
            if (is_bool($field->value())) {
                throw new InvalidArgumentException([
                    'key' => 'form.boolean.invalid'
                ]);
            }
        }
    }

    public static function date(Field $field)
    {
        if ($field->isEmpty() === false) {
            if (V::date($field->value()) !== true) {
                throw new InvalidArgumentException([
                    'key' => 'form.date.invalid'
                ]);
            }
        }
    }

    public static function email(Field $field)
    {
        if ($field->isEmpty() === false) {
            if (V::email($field->value()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.email.invalid'
                ]);
            }
        }
    }

    public static function length(Field $field)
    {
        static::minLength($field);
        static::maxLength($field);
    }

    public static function max(Field $field)
    {
        if ($field->isEmpty() === false && $field->max() !== null) {
            if ($field->value() > $field->max()) {
                throw new InvalidArgumentException([
                    'key' => 'form.max.invalid'
                ]);
            }
        }
    }

    public static function maxLength(Field $field)
    {
        if ($field->isEmpty() === false && $field->maxLength() !== null) {
            if (V::maxLength($field->value(), $field->maxLength()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.maxLength.invalid'
                ]);
            }
        }
    }

    public static function min(Field $field)
    {
        if ($field->isEmpty() === false && $field->min() !== null) {
            if ($field->value() < $field->min()) {
                throw new InvalidArgumentException([
                    'key' => 'form.min.invalid'
                ]);
            }
        }
    }

    public static function minLength(Field $field)
    {
        if ($field->isEmpty() === false && $field->minLength() !== null) {
            if (V::minLength($field->value(), $field->minLength()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.minLength.invalid'
                ]);
            }
        }
    }

    public static function minmax(Field $field)
    {
        static::min($field);
        static::max($field);
    }

    public static function multipleOptions(Field $field)
    {
        if ($field->isEmpty() === false) {
            $values = $field->optionValues();

            foreach ($field->value() as $key => $val) {
                if (in_array($val, $values, true) === false) {
                    throw new InvalidArgumentException([
                        'key' => 'form.option.invalid'
                    ]);
                }
            }
        }
    }

    public static function required(Field $field)
    {
        if ($field->isRequired() === true) {
            if ($field->isEmpty() === true) {
                throw new InvalidArgumentException([
                    'key' => 'form.field.required'
                ]);
            }
        }
    }

    public static function singleOption(Field $field)
    {
        if ($field->isEmpty() === false) {
            if (in_array($field->value(), $field->optionValues(), true) !== true) {
                throw new InvalidArgumentException([
                    'key' => 'form.option.invalid'
                ]);
            }
        }
    }

    public static function time(Field $field)
    {
        if ($field->isEmpty() === false) {
            if (V::time($field->value()) !== true) {
                throw new InvalidArgumentException([
                    'key' => 'form.time.invalid'
                ]);
            }
        }
    }

    public static function url(Field $field)
    {
        if ($field->isEmpty() === false) {
            if (V::url($field->value()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.url.invalid'
                ]);
            }
        }
    }
}
