<?php

use Kirby\Cms\Find;
use Kirby\Cms\UserRules;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Field;
use Kirby\Panel\Panel;
use Kirby\Toolkit\Escape;

$files = require __DIR__ . '/../files/dialogs.php';

return [

    // create
    'user.create' => [
        'pattern' => 'users/create',
        'load' => function () {
            $kirby = kirby();
            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'name'  => Field::username(),
                        'email' => Field::email([
                            'link'     => false,
                            'required' => true
                        ]),
                        'password'     => Field::password(),
                        'translation'  => Field::translation([
                            'required' => true
                        ]),
                        'role' => Field::role([
                            'required' => true
                        ])
                    ],
                    'submitButton' => t('create'),
                    'value' => [
                        'name'        => '',
                        'email'       => '',
                        'password'    => '',
                        'translation' => $kirby->panelLanguage(),
                        'role'        => $kirby->user()->role()->name()
                    ]
                ]
            ];
        },
        'submit' => function () {
            kirby()->users()->create([
                'name'     => get('name'),
                'email'    => get('email'),
                'password' => get('password'),
                'language' => get('translation'),
                'role'     => get('role')
            ]);
            return [
                'event' => 'user.create'
            ];
        }
    ],

    // change email
    'user.changeEmail' => [
        'pattern' => 'users/(:any)/changeEmail',
        'load' => function (string $id) {
            $user = Find::user($id);

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'email' => [
                            'label'     => t('email'),
                            'required'  => true,
                            'type'      => 'email',
                            'preselect' => true
                        ]
                    ],
                    'submitButton' => t('change'),
                    'value' => [
                        'email' => $user->email()
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            Find::user($id)->changeEmail(get('email'));
            return [
                'event' => 'user.changeEmail'
            ];
        }
    ],

    // change language
    'user.changeLanguage' => [
        'pattern' => 'users/(:any)/changeLanguage',
        'load' => function (string $id) {
            $user = Find::user($id);

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'translation' => Field::translation(['required' => true])
                    ],
                    'submitButton' => t('change'),
                    'value' => [
                        'translation' => $user->language()
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            Find::user($id)->changeLanguage(get('translation'));

            return [
                'event'  => 'user.changeLanguage',
                'reload' => [
                    'globals' => '$translation'
                ]
            ];
        }
    ],

    // change name
    'user.changeName' => [
        'pattern' => 'users/(:any)/changeName',
        'load' => function (string $id) {
            $user = Find::user($id);

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'name' => Field::username([
                            'preselect' => true
                        ])
                    ],
                    'submitButton' => t('rename'),
                    'value' => [
                        'name' => $user->name()->value()
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            Find::user($id)->changeName(get('name'));

            return [
                'event' => 'user.changeName'
            ];
        }
    ],

    // change password
    'user.changePassword' => [
        'pattern' => 'users/(:any)/changePassword',
        'load' => function (string $id) {
            $user = Find::user($id);

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'password' => Field::password([
                            'label' => t('user.changePassword.new'),
                        ]),
                        'passwordConfirmation' => Field::password([
                            'label' => t('user.changePassword.new.confirm'),
                        ])
                    ],
                    'submitButton' => t('change'),
                ]
            ];
        },
        'submit' => function (string $id) {
            $user                 = Find::user($id);
            $password             = get('password');
            $passwordConfirmation = get('passwordConfirmation');

            // validate the password
            UserRules::validPassword($user, $password ?? '');

            // compare passwords
            if ($password !== $passwordConfirmation) {
                throw new InvalidArgumentException([
                    'key' => 'user.password.notSame'
                ]);
            }

            // change password if everything's fine
            $user->changePassword($password);

            return [
                'event' => 'user.changePassword'
            ];
        }
    ],

    // change role
    'user.changeRole' => [
        'pattern' => 'users/(:any)/changeRole',
        'load' => function (string $id) {
            $user = Find::user($id);

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'role' => Field::role([
                            'label'    => t('user.changeRole.select'),
                            'required' => true,
                        ])
                    ],
                    'submitButton' => t('user.changeRole'),
                    'value' => [
                        'role' => $user->role()->name()
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            $user = Find::user($id)->changeRole(get('role'));

            return [
                'event' => 'user.changeRole',
                'user' => $user->toArray()
            ];
        }
    ],

    // delete
    'user.delete' => [
        'pattern' => 'users/(:any)/delete',
        'load' => function (string $id) {
            $user       = Find::user($id);
            $i18nPrefix = $user->isLoggedIn() ? 'account' : 'user';

            return [
                'component' => 'k-remove-dialog',
                'props' => [
                    'text' => tt($i18nPrefix . '.delete.confirm', [
                        'email' => Escape::html($user->email())
                    ])
                ]
            ];
        },
        'submit' => function (string $id) {
            $user     = Find::user($id);
            $redirect = false;
            $referrer = Panel::referrer();
            $url      = $user->panel()->url(true);

            $user->delete();

            // redirect to the users view
            // if the dialog has been opened in the user view
            if ($referrer === $url) {
                $redirect = '/users';
            }

            // logout the user if they deleted themselves
            if ($user->isLoggedIn()) {
                $redirect = '/logout';
            }

            return [
                'event'    => 'user.delete',
                'dispatch' => ['content/remove' => [$url]],
                'redirect' => $redirect
            ];
        }
    ],

    // change file name
    'user.file.changeName' => [
        'pattern' => '(users/.*?)/files/(:any)/changeName',
        'load'    => $files['changeName']['load'],
        'submit'  => $files['changeName']['submit'],
    ],

    // change file sort
    'user.file.changeSort' => [
        'pattern' => '(users/.*?)/files/(:any)/changeSort',
        'load'    => $files['changeSort']['load'],
        'submit'  => $files['changeSort']['submit'],
    ],

    // delete file
    'user.file.delete' => [
        'pattern' => '(users/.*?)/files/(:any)/delete',
        'load'    => $files['delete']['load'],
        'submit'  => $files['delete']['submit'],
    ]

];
