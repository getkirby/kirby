<?php

use Kirby\Toolkit\V;

return [
    'extends' => 'text',
    'type'    => 'url',
    'props'   => [
        'icon' => [
            'default' => 'url',
        ],
        'name' => [
            'default' => 'url'
        ],
        'label' => [
            'default' => 'Url'
        ],
        'placeholder' => [
            'default' => 'https://example.com'
        ],
    ],
    'methods' => [
        'validate' => function ($value) {
            return V::url($value);
        }
    ]
];
