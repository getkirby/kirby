<?php

use Kirby\Util\A;

return [
    'props' => [
        'placeholder' => [

        ],
    ],
    'computed' => [
        'placeholder' => [
            'set' => function ($value) {
                return $this->i18n($value);
            }
        ]
    ]
];
