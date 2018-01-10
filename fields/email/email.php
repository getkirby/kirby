<?php

use Kirby\Toolkit\V;

return [
    'extends' => 'text',
    'type'    => 'email',
    'props'   => [
        'autocomplete' => [
            'type'    => 'string',
            'default' => 'email'
        ],
        'icon' => [
            'default' => 'email',
        ],
        'name' => [
            'default' => 'email'
        ],
        'label' => [
            'default' => 'Email'
        ],
        'placeholder' => [
            'default' => 'mail@example.com'
        ],
    ],
    'methods' => [
        'validate' => function ($value) {
            return V::email($value);
        }
    ]
];
