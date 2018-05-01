<?php

return [
    'props' => [
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
            return $this->isEmpty($value) === false ? floatval($value) : null;
        }
    ],
    'validations' => [
        'required',
        'min',
        'max'
    ]
];
