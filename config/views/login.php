<?php

/** @var \Kirby\Cms\App $kirby */

return function () use ($kirby) {
    $status = $kirby->auth()->status();

    return [
        'component' => 'LoginView',
        'view'      => 'login',
        'props'     => [
            'methods' => array_keys($kirby->system()->loginMethods()),
            'pending' => [
                'email'     => $status->email(),
                'challenge' => $status->challenge()
            ]
        ],
    ];
};
