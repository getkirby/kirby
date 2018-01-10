<?php

use Kirby\Cms\App as Kirby;

return [
    'type'  => 'author',
    'props' => [
        'default' => [
            'default' => function () {
                return Kirby::instance()->user()->id();
            }
        ],
        'icon' => [
            'default' => 'user',
        ],
        'label' => [
            'default' => 'Author'
        ],
        'name' => [
            'default' => 'author'
        ],
        'value' => [
            'type' => 'string'
        ]
    ],
    'methods' => [
        'validate' => function ($value) {
            return Kirby::instance()->user($value) !== null;
        }
    ]

];
