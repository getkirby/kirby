<?php

use Kirby\Panel\Panel;

return function ($kirby) {
    return [
        'icon'   => 'account',
        'label'  => t('view.account'),
        'search' => 'users',
        'views'  => [
            'account' => [
                'pattern' => 'account',
                'action'  => function () use ($kirby) {
                    return [
                        'component' => 'k-account-view',
                        'props'     => $kirby->user()->panel()->props(),
                    ];
                },
            ],
            'logout' => [
                'pattern' => 'logout',
                'auth'    => false,
                'action'  => function () use ($kirby) {
                    if ($user = $kirby->user()) {
                        $user->logout();
                    }
                    Panel::go('login');
                },
            ],
            'password' => [
                'pattern' => 'reset-password',
                'action'  => function () {
                    return [
                        'component' => 'k-reset-password-view',
                    ];
                }
            ]
        ]
    ];
};
