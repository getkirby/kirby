<?php

return [
    'props' => [
        'min' => function (float $min = 0) {
            return $min;
        },
        'min' => function (float $max = 0) {
            return $max;
        },
        'step' => function (float $step = 1) {
            return $step;
        },
        'tooltip' => function ($tooltip = true) {
            return $tooltip;
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
