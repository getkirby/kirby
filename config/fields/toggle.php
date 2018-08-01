<?php

use Kirby\Exception\InvalidArgumentException;

return [
    'props' => [
        'default' => function ($value = null) {
            return $this->toBool($value);
        },
        'text' => function ($value = null) {

            if (is_array($value) === true) {

                if (A::isAssociative($value) === true) {
                    return I18n::translate($value, $value);
                }

                foreach ($value as $key => $val) {
                    $value[$key] = I18n::translate($val, $val);
                }

                return $value;

            }

            return I18n::translate($value, $value);

        },
    ],
    'computed' => [
        'value' => function () {
            if ($this->props['value'] === null) {
                return $this->default();
            } else {
                return $this->toBool($this->props['value']);
            }
        }
    ],
    'methods' => [
        'toBool' => function ($value) {
            return in_array($value, [true, 'true', 1, '1', 'on'], true) === true;
        },
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
