<?php

use Kirby\Cms\Find;

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
    ]
];
