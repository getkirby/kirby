<?php

use Kirby\Toolkit\Str;
use Kirby\Toolkit\Pagination;

return function ($kirby) {

    Inertia::setup($kirby);

    // Panel is not installed yet
    if ($kirby->system()->isOk() === false || $kirby->system()->isInstalled() === false) {
        return [
            [
                'pattern' => 'browser',
                'action'  => require __DIR__ . '/views/browser.php'
            ],
            [
                'pattern' => 'installation',
                'action'  => require __DIR__ . '/views/installation.php'
            ],
            [
                'pattern' => '(:all)',
                'action'  => function () {
                    go('panel/installation');
                }
            ]
        ];
    }

    // No session yet
    if (!$kirby->user()) {
        return [
            [
                'pattern' => 'browser',
                'action'  => require __DIR__ . '/views/browser.php'
            ],
            [
                'pattern' => 'login',
                'action'  => require __DIR__ . '/views/login.php'
            ],
            [
                'pattern' => '(:all)',
                'action'  => function () {
                    go('panel/login');
                }
            ]
        ];
    }

    // Language switcher
    if ($kirby->options('languages')) {
        if ($lang = get('language')) {
            $kirby->session()->set('language', $lang);
        }

        $kirby->setCurrentLanguage($kirby->session()->get('language', 'en'));
    }

    // Installed and logged in
    return [
        [
            'pattern' => [
                '/',
                'installation',
                'login',
            ],
            'action'  => function () {
                go('panel/site');
            }
        ],
        [
            'pattern' => 'account',
            'action'  => require __DIR__ . '/views/account.php',
        ],
        [
            'pattern' => 'browser',
            'action'  => require __DIR__ . '/views/browser.php',
        ],
        [
            'pattern' => 'logout',
            'action'  => function () use ($kirby) {
                $kirby->user()->logout();
                go('panel/login');
            }
        ],
        [
            'pattern' => 'pages/(:any)',
            'action'  => require __DIR__ . '/views/page.php'
        ],
        [
            'pattern' => 'pages/(:any)/files/(:any)',
            'action'  => require __DIR__ . '/views/page.file.php'
        ],
        [
            'pattern' => 'settings',
            'action'  => require __DIR__ . '/views/settings.php'
        ],
        [
            'pattern' => 'site',
            'action'  => require __DIR__ . '/views/site.php'
        ],
        [
            'pattern' => 'site/files/(:any)',
            'action'  => require __DIR__ . '/views/site.file.php'
        ],
        [
            'pattern' => [
                'users',
                'users/role/(:any)'
            ],
            'action'  => require __DIR__ . '/views/users.php'
        ],
        [
            'pattern' => 'users/(:any)',
            'action'  => require __DIR__ . '/views/user.php'
        ],
        [
            'pattern' => 'users/(:any)/files/(:any)',
            'action'  => require __DIR__ . '/views/user.file.php'
        ],
        [
            'pattern' => '(:all)',
            'action'  => function () use ($kirby) {
                return 'The view could not be found';
            }
        ],
    ];

};
