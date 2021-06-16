<?php

use Kirby\Cms\Find;
use Kirby\Panel\Field;

return [
    // delete language
    'languages/(:any)/delete' => [
        'load' => function (string $id) {
            $language = Find::language($id);
            return [
                'component' => 'k-remove-dialog',
                'props' => [
                    'text' => tt('language.delete.confirm', [
                        'name' => $language->name()
                    ])
                ]
            ];
        },
        'submit' => function (string $id) {
            Find::language($id)->delete();
            return [
                'event' => 'language.delete',
            ];
        }
    ],

    // license registration
    'registration' => [
        'load' => function () {
            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'license' => [
                            'label'       => t('license.register.label'),
                            'type'        => 'text',
                            'required'    => true,
                            'counter'     => false,
                            'placeholder' => 'K3-',
                            'help'        => t('license.register.help')
                        ],
                        'email' => Field::email([
                            'required' => true
                        ])
                    ],
                    'submitButton' => t('license.register'),
                    'value' => [
                        'license' => null,
                        'email'   => null
                    ]
                ]
            ];
        },
        'submit' => function () {
            kirby()->system()->register(get('license'), get('email'));
            return [
                'event'   => 'system.register',
                'message' => t('license.register.success')
            ];
        }
    ],
];
