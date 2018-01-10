<?php

use Kirby\Toolkit\V;

return [
    'props' => [
        'format' => [
            'default' => DATE_W3C,
        ],
        'override' => [
            'default' => false,
            'type'    => 'boolean'
        ],
    ],
    'methods' => [
        'createDataValue' => function ($value) {

            if ($value !== null && $date = strtotime($value)) {
                return date(DATE_W3C, $date);
            }

            return null;

        },
        'createTextValue' => function ($value) {

            if ($date = strtotime($value)) {
                return date($this->format, $date);
            }

            return null;

        },
        'validate' => function ($value) {
            return V::date($value);
        }
    ]
];
