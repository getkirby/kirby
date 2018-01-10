<?php

use Kirby\Toolkit\V;

return [
    'type'   => 'time',
    'props'  => [
        'icon' => [
            'default' => 'clock',
        ],
        '12h' => [
            'default'  => false,
            'type'     => 'boolean',
        ],
        'step' => [
            'default' => 60,
            'type'    => 'integer'
        ],
        'label' => [
            'default' => 'Time'
        ],
        'name' => [
            'default' => 'time'
        ],
        'override' => [
            'default' => false,
            'type'    => 'boolean'
        ],
        'value' => [
            'type' => 'string'
        ]
    ],
    'methods' => [
        'createDataValue' => function ($value) {
            if ($timestamp = strtotime($value)) {
                return date('H:i:s', $timestamp);
            }

            return null;
        },
        'createTextValue' => function ($value) {
            if ($timestamp = strtotime($value)) {
                return date('H:i:s', $timestamp);
            }

            return null;
        },
        'validate' => function ($value) {
            return V::time($value);
        }
    ]

];
