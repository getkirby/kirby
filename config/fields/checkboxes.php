<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [
    'mixins' => ['options'],
    'props' => [
        'default' => function ($default = null) {
            return Str::split($default, ',');
        },
        'max' => function (int $max = null) {
            return $max;
        },
        'min' => function (int $min = null) {
            return $min;
        },
        'value' => function ($value = null) {
            return Str::split($value, ',');
        },
    ],
    'computed' => [
        'options' => function (): array {
            return $this->getOptions();
        },
        'default' => function () {
            return $this->sanitizeOptions($this->default);
        },
        'value' => function () {
            return $this->sanitizeOptions($this->value);
        },
    ],
    'toString' => function ($value): string {
        return A::join($value, ', ');
    },
    'validations' => [
        'options',
        'max',
        'min'
    ]
];
