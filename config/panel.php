<?php

use Kirby\Http\Response;
use Kirby\Toolkit\View;

return function ($kirby) {

    $system = $kirby->system();

    /**
     * Route for browser compatibility check
     */
    $routes = [
        [
            'pattern' => 'browser',
            'action'  => function () use ($kirby) {
                $view = new View($kirby->root('kirby') . '/views/browser.php');
                return new Response($view->render());
            }
        ]
    ];

    /**
     * Panel isn't installed yet
     */
    if (
        $system->isOk() === false ||
        $system->isInstalled() === false
    ) {
        return array_merge($routes, [
            [
                'pattern' => 'installation',
                'action'  => function () use ($kirby, $system) {

                    return [
                        'component' => 'InstallationView',
                        'view'      => 'installation',
                        'props'     => [
                            'isInstallable' => $system->isInstallable(),
                            'isInstalled'   => $system->isInstalled(),
                            'isOk'          => $system->isOk(),
                            'requirements'  => $system->status(),
                            'translations'  => $kirby->translations()->values(function ($translation) {
                                return [
                                    'text'  => $translation->name(),
                                    'value' => $translation->code(),
                                ];
                            }),
                        ]
                    ];
                }
            ],
            [
                'pattern' => '(:all)',
                'action'  => function () {
                    go('panel/installation');
                }
            ]
        ]);
    }

    /**
     * User is not logged in
     */
    if (!$kirby->user()) {
        return array_merge($routes, [
            [
                'pattern' => 'login',
                'action'  => function () use ($kirby, $system) {
                    $status = $kirby->auth()->status();

                    return [
                        'component' => 'LoginView',
                        'view'      => 'login',
                        'props'     => [
                            'methods' => array_keys($system->loginMethods()),
                            'pending' => [
                                'email'     => $status->email(),
                                'challenge' => $status->challenge()
                            ]
                        ],
                    ];
                }
            ],
            [
                'pattern' => '(:all)',
                'action'  => function () {
                    go('panel/login');
                }
            ]
        ]);
    }

    /**
     * Installed and authenticates
     */

    // Language switcher
    if ($kirby->options('languages')) {
        if ($lang = get('language')) {
            $kirby->session()->set('language', $lang);
        }

        $kirby->setCurrentLanguage($kirby->session()->get('language', 'en'));
    }

    return array_merge($routes, [
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
            'action'  => function () use ($kirby) {
                return [
                    'component' => 'AccountView',
                    'props'     => $kirby->user()->panel()->props(),
                    'view'      => 'account'
                ];
            },
        ],
        [
            'pattern' => 'logout',
            'action'  => function () use ($kirby) {
                //TODO: localStorage doesn't get cleared anymore
                $kirby->user()->logout();
                go('panel/login');
            }
        ],
        [
            'pattern' => 'pages/(:any)',
            'action'  => function (string $path) use ($kirby) {
                if (!$page = $kirby->page(str_replace('+', '/', $path))) {
                    return t('error.page.undefined');
                }

                return $page->panel()->route();
            }
        ],
        [
            'pattern' => 'pages/(:any)/files/(:any)',
            'action'  => function (string $id, string $filename) use ($kirby) {
                $id       = str_replace('+', '/', $id);
                $filename = urldecode($filename);

                if (!$page = $kirby->page($id)) {
                    return t('error.page.undefined');
                }

                if (!$file = $page->file($filename)) {
                    return t('error.file.undefined');
                }

                return $file->panel()->route();
            }
        ],
        [
            'pattern' => 'settings',
            'action'  => function () use ($kirby) {
                return [
                    'component' => 'SettingsView',
                    'view'      => 'settings',
                    'props'     => [
                        'languages' => $kirby->languages()->values(function ($language) {
                            return [
                                'default' => $language->isDefault(),
                                'icon' => [
                                    'back' => 'black',
                                    'type' => 'globe',
                                ],
                                'id' => $language->code(),
                                'image' => true,
                                'info' => $language->code(),
                                'text' => $language->name(),
                            ];
                        }),
                        'version' => $kirby->version(),
                    ]
                ];
            }
        ],
        [
            'pattern' => 'site',
            'action'  => function () use ($kirby) {
                return $kirby->site()->panel()->route();
            }
        ],
        [
            'pattern' => 'site/files/(:any)',
            'action'  => function (string $filename) use ($kirby) {
                $filename = urldecode($filename);

                if (!$file = $kirby->site()->file($filename)) {
                    return t('error.file.undefined');
                }

                return $file->panel()->route();
            }
        ],
        [
            'pattern' => [
                'users',
                'users/role/(:any)'
            ],
            'action'  => function (string $role = null) use ($kirby) {
                $roles = $kirby->roles();

                return [
                    'component' => 'UsersView',
                    'view'      => 'users',
                    'props'     => [
                        'role' => function () use ($kirby, $roles, $role) {
                            if ($role && $role = $roles->find($role)) {
                                return [
                                    'id'    => $role->id(),
                                    'title' => $role->title()
                                ];
                            }
                        },
                        'roles' => $roles->values(function ($role) {
                            return [
                                'id'    => $role->id(),
                                'title' => $role->title(),
                            ];
                        }),
                        'users' => function () use ($kirby, $role) {
                            $users = $kirby->users();

                            if (empty($role) === false) {
                                $users = $users->role($role);
                            }

                            return $users->values(function ($user) {
                                return [
                                    'id'   => $user->id(),
                                    'icon' => [
                                        'type' => 'user',
                                        'back' => 'black'
                                    ],
                                    'text'  => $user->username(),
                                    'info'  => $user->role()->title(),
                                    'link'  => 'users/' . $user->id(),
                                    'image' => true
                                ];
                            }, [
                                'limit' => 20,
                                'page'  => get('page')
                            ]);
                        },
                    ]
                ];
            }
        ],
        [
            'pattern' => 'users/(:any)',
            'action'  => function (string $id) use ($kirby) {
                if (!$user = $kirby->user($id)) {
                    return t('error.user.undefined');
                }

                return $user->panel()->route();
            }
        ],
        [
            'pattern' => 'users/(:any)/files/(:any)',
            'action'  => function (string $id, string $filename) use ($kirby) {
                $filename = urldecode($filename);

                if (!$user = $kirby->user($id)) {
                    return t('error.user.undefined');
                }

                if (!$file = $user->file($filename)) {
                    return t('error.file.undefined');
                }

                return $file->panel()->route();
            }
        ],
        [
            'pattern' => 'reset-password',
            'action'  => function () {
                return [
                    'component' => 'ResetPasswordView',
                    'view'      => 'reset-password'
                ];
            }
        ],
        [
            'pattern' => 'plugins/(:any)',
            'action'  => function (string $id) {
                return [
                    'component' => 'PluginView',
                    'view'      => $id,
                    'props' => [
                        'id' => $id
                    ]
                ];
            }
        ],
        [
            'pattern' => '(:all)',
            'action'  => function () {
                return 'The view could not be found';
            }
        ],
    ]);
};
