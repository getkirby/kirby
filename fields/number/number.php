<?php

use Kirby\Toolkit\V;

return [
    'type' => 'number',
    'props' => [
        'max' => [
            'type' => 'number'
        ],
        'min' => [
            'type' => 'number'
        ],
        'step' => [
            'type' => 'number'
        ],
        'value' => [
            'type' => 'double',
        ]
    ],
    'methods' => [
        'createDataValue' => function ($value) {
            return floatval($value);
        },
        'validate' => function ($value) {
            $result = true;
            $max    = $this->max();
            $min    = $this->min();

            if ($max && V::max($value, $max) === false) {
                return false;
            }

            if ($min && V::min($value, $min) === false) {
                return false;
            }

            return true;
        }
    ]
];
