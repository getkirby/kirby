<?php

/** @var \Kirby\Cms\App $kirby */

return function (string $id) use ($kirby) {
    if (!$user = $kirby->user($id)) {
        return t('error.user.undefined');
    }

    return [
        'component' => 'UserView',
        'props' => Inertia::model($user, [
            'next' => Inertia::prevnext($user->next(), 'username'),
            'prev' => Inertia::prevnext($user->prev(), 'username'),
            'user' => [
                'avatar'   => Inertia::avatar($user),
                'content'  => Inertia::content($user),
                'email'    => $user->email(),
                'id'       => $user->id(),
                'language' => $user->panel()->translation()->name(),
                'name'     => $user->name()->toString(),
                'role'     => $user->role()->title(),
                'username' => $user->username(),
            ],
        ]),
        'view' => [
            'breadcrumb' => [
                [
                    'label' => $user->username(),
                    'link'  => $user->panel()->url(true),
                ]
            ],
            'id'    => 'user',
            'title' => $user->username(),
        ]
    ];
};
