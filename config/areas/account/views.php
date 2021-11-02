<?php

use Kirby\Cms\Find;
use Kirby\Panel\Panel;

return [
    'account' => [
        'pattern' => 'account',
        'action'  => function () {
            return [
                'component' => 'k-account-view',
                'props'     => kirby()->user()->panel()->props(),
            ];
        },
    ],
    'account.file' => [
        'pattern' => 'account/files/(:any)',
        'action'  => function (string $filename) {
            return Find::file('account', $filename)->panel()->view();
        }
    ],
    'account.logout' => [
        'pattern' => 'logout',
        'auth'    => false,
        'action'  => function () {
            if ($user = kirby()->user()) {
                $user->logout();
            }
            Panel::go('login');
        },
    ],
    'account.password' => [
        'pattern' => 'reset-password',
        'action'  => function () {
            return [
                'component' => 'k-reset-password-view',
            ];
        }
    ]
];
