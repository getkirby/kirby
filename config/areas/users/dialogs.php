<?php

use Kirby\Cms\Find;
use Kirby\Cms\UserRules;
use Kirby\Exception\InvalidArgumentException;

return [

    // change email
    'users/(:any)/changeEmail' => [
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
                'event'    => 'user.changeEmail',
                'dispatch' => [
                    'content/revert' => ['users/' . $id]
                ]
            ];
        }
    ],

    // change language
    'users/(:any)/changeLanguage' => [
        'load' => function (string $id) {
            $user    = Find::user($id);
            $options = [];

            foreach (kirby()->translations() as $translation) {
                $options[] = [
                    'text'  => $translation->name(),
                    'value' => $translation->code()
                ];
            }

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'language' => [
                            'label'     => t('language'),
                            'type'      => 'select',
                            'icon'      => 'globe',
                            'options'   => $options,
                            'required'  => true,
                            'empty'     => false
                        ]
                    ],
                    'submitButton' => t('change'),
                    'value' => [
                        'language' => $user->language()
                    ]
                ]
            ];
        },
        'submit' => function (string $id) {
            Find::user($id)->changeLanguage(get('language'));

            return [
                'event'  => 'user.changeLanguage',
                'reload' => [
                    'only' => '$translation'
                ]
            ];
        }
    ],

    // change name
    'users/(:any)/changeName' => [
        'load' => function (string $id) {
            $user = Find::user($id);

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'name' => [
                            'label'     => t('name'),
                            'type'      => 'text',
                            'icon'      => 'user',
                            'preselect' => true
                        ]
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
    'users/(:any)/changePassword' => [
        'load' => function (string $id) {
            $user = Find::user($id);

            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'password' => [
                            'label' => t('user.changePassword.new'),
                            'type'  => 'password',
                            'icon'  => 'key',
                        ],
                        'passwordConfirmation' => [
                            'label' => t('user.changePassword.new.confirm'),
                            'type'  => 'password',
                            'icon'  => 'key',
                        ]
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
            UserRules::validPassword($user, $password);

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

    // delete
    'users/(:any)/delete' => [
        'load' => function (string $id) {
            $user = Find::user($id);

            return [
                'component' => 'k-remove-dialog',
                'props' => [
                    'text' => tt('user.delete.confirm', [
                        'email' => $user->email()
                    ])
                ]
            ];
        },
        'submit' => function (string $id) {
            Find::user($id)->delete();

            return [
                'event'    => 'user.delete',
                'dispatch' => [
                    'content/remove' => ['users/' . $id]
                ]
            ];
        }
    ],

];
