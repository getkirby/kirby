<?php

use Kirby\Toolkit\Str;

return [
    'props' => [
        /**
         * The lowest allowed number
         */
        'min' => function (float $min = null) {
            return $min;
        },
        /**
         * The highest allowed number
         */
        'max' => function (float $max = null) {
            return $max;
        },
        /**
         * Allowed incremental steps between numbers (i.e `0.5`)
         */
        'step' => function ($step = null) {
            return $this->toNumber($step);
        }
    ],
    'computed' => [
        'default' => function () {
            return $this->toNumber($this->default);
        },
        'value' => function () {
            return $this->toNumber($this->value);
        }
    ],
    'methods' => [
        'toNumber' => function ($value) {
            if ($this->isEmpty($value) === true) {
                return null;
            }

            return is_float($value) === true ? $value : (float)Str::float($value);
        }
    ],
    'validations' => [
        'min',
        'max'
    ]
];
