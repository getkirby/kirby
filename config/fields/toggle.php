<?php

use Kirby\Exception\InvalidArgumentException;

return [
    'props' => [
        'text' => function ($value = null) {
            return I18n::translate($value, $value);
        },
        'value' => function ($value = null) {
            return in_array($value, [true, 'true', 1, '1', 'on'], true) === true;
        }
    ],
    'methods' => [
        'toString' => function ($value): string {
            return $value === true ? 'true' : 'false';
        }
    ],
    'validations' => [
        'boolean',
        'required' => function ($value) {
            if ($this->isRequired() && ($value === false || $this->isEmpty($value))) {
                throw new InvalidArgumentException([
                    'key' => 'form.field.required'
                ]);
            }
        },
    ]
];
