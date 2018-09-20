<?php

return [
    'props' => [
        'default' => function ($default = null) {
            return $this->toNumber($default);
        },
        'min' => function (float $min = 0) {
            return $min;
        },
        'max' => function (float $max = null) {
            return $max;
        },
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
