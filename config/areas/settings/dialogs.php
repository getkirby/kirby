<?php

use Kirby\Cms\Find;
use Kirby\Panel\Field;

return [

    // create language
    'languages/create' => [
        'load' => function () {
            return [
                'component' => 'k-language-dialog',
                'props' => [
                    'fields' => [
                        'name' => [
                            'label'    => t('language.name'),
                            'type'     => 'text',
                            'required' => true,
                            'icon'     => 'title'
                        ],
                        'code' => [
                            'label'    => t('language.code'),
                            'type'     => 'text',
                            'required' => true,
                            'counter'  => false,
                            'icon'     => 'globe',
                            'width'    => '1/2'
                        ],
                        'direction' => [
                            'label'    => t('language.direction'),
                            'type'     => 'select',
                            'required' => true,
                            'empty'    => false,
                            'options'  => [
                                ['value' => 'ltr', 'text' => t('language.direction.ltr')],
                                ['value' => 'rtl', 'text' => t('language.direction.rtl')]
                            ],
                            'width'    => '1/2'
                        ],
                        'locale' => [
                            'label' => t('language.locale'),
                            'type'  => 'text',
                        ],
                    ],
                    'submitButton' => t('language.create'),
                    'value' => [
                        'name'      => '',
                        'code'      => '',
                        'direction' => 'ltr',
                        'locale'    => ''
                    ]
                ]
            ];
        },
        'submit' => function () {
            kirby()->languages()->create(get());
            return [
                'event' => 'language.create'
            ];
        }
    ],

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
