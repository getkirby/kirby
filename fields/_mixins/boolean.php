<?php

use Kirby\Toolkit\V;

return [
    'props'   => [
        'value' => [
            'type' => 'boolean'
        ]
    ],
    'methods' => [
        'createDataValue' => function ($value) {
            return in_array($value, [true, 'true', 1, '1', 'on'], true) === true;
        },
        'createTextValue' => function ($value) {
            return $value === true ? 'true' : 'false';
        }
    ]
];
