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
        'min' => function (float $min = 0) {
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
        'step' => function (float $step = 1) {
            return $step;
        },
        'value' => function ($value = null) {
            return $this->toNumber($value);
        }
    ],
    'methods' => [
        'toNumber' => function ($value) {
            return $this->isEmpty($value) === false ? floatval($value) : null;
        }
    ],
    'validations' => [
        'min',
        'max'
    ]
];
