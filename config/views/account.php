<?php

/** @var \Kirby\Cms\App $kirby */

return function () use ($kirby) {
    return [
        'component' => 'AccountView',
        'props' => Inertia::model($user = $kirby->user(), [
            'user' => [
                'avatar'   => Inertia::avatar($user),
                'content'  => Inertia::content($user),
                'email'    => $user->email(),
                'id'       => $user->id(),
                'language' => $user->panel()->translation()->name(),
                'name'     => $user->name()->toString(),
                'role'     => $user->role()->title(),
                'username' => $user->username(),
            ]
        ]),
        'view' => 'account'
    ];
};
