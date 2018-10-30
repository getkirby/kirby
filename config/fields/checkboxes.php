<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [
    'mixins' => ['options'],
    'props' => [
        /**
         * Arranges the checkboxes in the given number of columns
         */
        'columns' => function (int $columns = 1) {
            return $columns;
        },
        /**
         * Default value for the field, which will be used when a Page/File/User is created
         */
        'default' => function ($default = null) {
            return Str::split($default, ',');
        },
        /**
         * Maximum number of checked boxes
         */
        'max' => function (int $max = null) {
            return $max;
        },
        /**
         * Minimum number of checked boxes
         */
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
    'save' => function ($value): string {
        return A::join($value, ', ');
    },
    'validations' => [
        'options',
        'max',
        'min'
    ]
];
