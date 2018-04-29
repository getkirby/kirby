<?php

use Kirby\Util\I18n;

return [
    'props' => [
        'counter'    => function ($value = null) {
            if ($this->maxLength() !== null || $this->minLength() !== null) {
                return true;
            }

            return $value ?? false;
        },
        'icon'        => null,
        'maxLength'   => null,
        'minLength'   => null,
        'multiline'   => true,
        'placeholder' => function ($value = null) {
            return I18n::translate($value, $value);
        },
        'required'    => false,
        'size'        => null
    ],
    'methods' => [
        'toString' => function ($value): string {
            return trim($value);
        },
        'validate' => function () {
            $this->validate('required');
            $this->validate('length');
        }
    ]
];
