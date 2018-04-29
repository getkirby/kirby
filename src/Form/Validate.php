<?php

namespace Kirby\Form;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\V;

class Validate
{

    public static function boolean(Field $field, string $prop)
    {
        if ($field->isEmpty() === false) {
            if (is_bool($field->{$prop}())) {
                throw new InvalidArgumentException([
                    'key' => 'form.boolean.invalid'
                ]);
            }
        }
    }

    public static function date(Field $field, string $prop)
    {
        if ($field->isEmpty() === false) {
            if (V::date($field->{$prop}()) !== true) {
                throw new InvalidArgumentException([
                    'key' => 'form.date.invalid'
                ]);
            }
        }
    }

    public static function email(Field $field, string $prop)
    {
        if ($field->isEmpty() === false) {
            if (V::email($field->{$prop}()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.email.invalid'
                ]);
            }
        }
    }

    public static function length(Field $field, string $prop)
    {
        static::minLength($field, $prop);
        static::maxLength($field, $prop);
    }

    public static function max(Field $field, string $prop)
    {
        if ($field->isEmpty() === false && $field->max() !== null) {
            if ($field->{$prop}() > $field->max()) {
                throw new InvalidArgumentException([
                    'key' => 'form.max.invalid'
                ]);
            }
        }
    }

    public static function maxLength(Field $field, string $prop)
    {
        if ($field->isEmpty() === false && $field->maxLength() !== null) {
            if (V::maxLength($field->{$prop}(), $field->maxLength()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.maxLength.invalid'
                ]);
            }
        }
    }

    public static function min(Field $field, string $prop)
    {
        if ($field->isEmpty() === false && $field->min() !== null) {
            if ($field->{$prop}() < $field->min()) {
                throw new InvalidArgumentException([
                    'key' => 'form.min.invalid'
                ]);
            }
        }
    }

    public static function minLength(Field $field, string $prop)
    {
        if ($field->isEmpty() === false && $field->minLength() !== null) {
            if (V::minLength($field->{$prop}(), $field->minLength()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.minLength.invalid'
                ]);
            }
        }
    }

    public static function minmax(Field $field, string $prop)
    {
        static::min($field);
        static::max($field);
    }

    public static function multipleOptions(Field $field, string $prop)
    {
        if ($field->isEmpty() === false) {
            $values = $field->optionValues();

            foreach ($field->{$prop}() as $key => $val) {
                if (in_array($val, $values, true) === false) {
                    throw new InvalidArgumentException([
                        'key' => 'form.option.invalid'
                    ]);
                }
            }
        }
    }

    public static function required(Field $field, string $prop)
    {
        if ($field->isRequired() === true) {
            if ($field->isEmpty() === true) {
                throw new InvalidArgumentException([
                    'key' => 'form.field.required'
                ]);
            }
        }
    }

    public static function singleOption(Field $field, string $prop)
    {
        if ($field->isEmpty() === false) {
            if (in_array($field->{$prop}(), $field->optionValues(), true) !== true) {
                throw new InvalidArgumentException([
                    'key' => 'form.option.invalid'
                ]);
            }
        }
    }

    public static function time(Field $field, string $prop)
    {
        if ($field->isEmpty() === false) {
            if (V::time($field->{$prop}()) !== true) {
                throw new InvalidArgumentException([
                    'key' => 'form.time.invalid'
                ]);
            }
        }
    }

    public static function url(Field $field, string $prop)
    {
        if ($field->isEmpty() === false) {
            if (V::url($field->{$prop}()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.url.invalid'
                ]);
            }
        }
    }
}
