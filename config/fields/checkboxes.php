<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [
    'mixins' => ['options'],
    'props' => [
        'default' => function ($default = null) {
            return Str::split($default, ',');
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
        'options'
    ]
];
