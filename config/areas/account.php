<?php

use Kirby\Cms\Find;
use Kirby\Panel\Panel;

$dialogs = require __DIR__ . '/files/dialogs.php';

return function ($kirby) {
    return [
        'icon'   => 'account',
        'label'  => t('view.account'),
        'search' => 'users',
        'views'  => [
            [
                'pattern' => 'account',
                'action'  => function () use ($kirby) {
                    return [
                        'component' => 'k-account-view',
                        'props'     => $kirby->user()->panel()->props(),
                    ];
                },
            ],
            [
                'pattern' => 'logout',
                'auth'    => false,
                'action'  => function () use ($kirby) {
                    if ($user = $kirby->user()) {
                        $user->logout();
                    }
                    Panel::go('login');
                },
            ],
            [
                'pattern' => 'reset-password',
                'action'  => function () {
                    return [
                        'component' => 'k-reset-password-view',
                    ];
                }
            ],
            [
                'pattern' => 'account/files/(:any)',
                'action'  => function (string $filename) {
                    return Find::file('account', $filename)->panel()->route();
                }
            ]
        ],
        'dialogs' => [
            // change file name
            'account/files/(:any)/changeName' => $dialogs['changeName'],

            // change file sort
            'account/files/(:any)/changeSort' => $dialogs['changeSort'],

            // delete file
            'account/files/(:any)/delete' => $dialogs['delete'],
        ]
    ];
};
