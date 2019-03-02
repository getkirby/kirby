<?php

return [
    'props' => [
        /**
         * Default number that will be saved when a new Page/User/File is created
         */
        'default' => function ($default = null) {
            return $this->toNumber($default);
        },
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
         * Allowed incremental steps between numbers (i.e 0.5)
         */
        'step' => function ($step = 1) {
            return $this->toNumber($step);
        },
        'value' => function ($value = null) {
            return $this->toNumber($value);
        }
    ],
    'methods' => [
        'toNumber' => function ($value) {
            if ($this->isEmpty($value) === true) {
                return null;
            }

            return (float)Str::float($value);
        }
    ],
    'validations' => [
        'min',
        'max'
    ]
];
