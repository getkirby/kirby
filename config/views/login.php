<?php

/** @var \Kirby\Cms\App $kirby */

return function () use ($kirby) {
    return [
        'component' => 'LoginView',
        'props' => [
            'methods' => array_keys($kirby->system()->loginMethods()),
            'pending' => [
                'email'     => null,
                'challenge' => null
            ]

        ],
        'view'      => 'login'
    ];
};
