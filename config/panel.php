<?php

use Kirby\Panel\Panel;
use Kirby\Http\Response;
use Kirby\Toolkit\View;

return function ($kirby) {

    $session = $kirby->session();
    $system  = $kirby->system();
    $user    = $kirby->user();


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
                    Panel::go('installation');
                }
            ]
        ]);
    }


    /**
     * User is not logged in
     */
    if (!$user) {
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
                'action'  => function ($path) use ($kirby, $session) {
                    /**
                     * Store the current path in the session
                     * Once the user is logged in, the path will
                     * be used to redirect to that view again
                     */
                    $session->set('panel.path', $path);
                    Panel::go('login');
                }
            ]
        ]);
    }


    /**
     * No panel access
     */
    if ($user->role()->permissions()->for('access', 'panel') === false) {
        return array_merge($routes, [
            [
                'pattern' => '(:all)',
                'action'  => function () {
                    go();
                }
            ]
        ]);
    }


    /**
     * Language switcher
     */
    if ($kirby->options('languages')) {
        if ($lang = get('language')) {
            $session->set('panel.language', $lang);
        }

        $kirby->setCurrentLanguage($session->get('panel.language', 'en'));
    }


    /**
     * Installed and authenticated
     */
    return array_merge($routes, [
        [
            'pattern' => [
                '/',
                'installation',
                'login',
            ],
            'action' => function () use ($kirby, $session) {
                /**
                 * If the last path has been stored in the
                 * session, redirect the user to it
                 */
                $path = trim($session->get('panel.path'), '/');

                // ignore various paths when redirecting
                if (in_array($path, ['', 'login', 'logout', 'installation'])) {
                    $path = 'site';
                }

                Panel::go($path);
            }
        ],
        [
            'pattern' => 'account',
            'action'  => function () use ($kirby, $user) {
                return [
                    'component' => 'AccountView',
                    'props'     => $user->panel()->props(),
                    'view'      => 'account'
                ];
            },
        ],
        [
            'pattern' => 'logout',
            'action'  => function () use ($kirby, $user) {
                //TODO: localStorage doesn't get cleared anymore
                $user->logout();
                Panel::go('login');
            }
        ],
        [
            'pattern' => 'pages/(:any)',
            'access'  => 'site',
            'action'  => function (string $path) use ($kirby) {
                if (!$page = $kirby->page(str_replace('+', '/', $path))) {
                    return t('error.page.undefined');
                }

                return $page->panel()->route();
            }
        ],
        [
            'pattern' => 'pages/(:any)/files/(:any)',
            'access'  => 'site',
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
            'access'  => 'settings',
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
            'access'  => 'site',
            'action'  => function () use ($kirby) {
                return $kirby->site()->panel()->route();
            }
        ],
        [
            'pattern' => 'site/files/(:any)',
            'access'  => 'site',
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
            'access'  => 'users',
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

                            $users = $users->paginate([
                                'limit' => 20,
                                'page'  => get('page')
                            ]);

                            return [
                                'data' => $users->values(function ($user) {
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
                                }),
                                'pagination' => $users->pagination()->toArray()
                            ];
                        },
                    ]
                ];
            }
        ],
        [
            'pattern' => 'users/(:any)',
            'access'  => 'users',
            'action'  => function (string $id) use ($kirby) {
                if (!$user = $kirby->user($id)) {
                    return t('error.user.undefined');
                }

                return $user->panel()->route();
            }
        ],
        [
            'pattern' => 'users/(:any)/files/(:any)',
            'access'  => 'users',
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
