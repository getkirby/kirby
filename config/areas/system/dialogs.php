<?php

use Kirby\Panel\Field;

return [
    // system issue information
    'system.issue' => [
        'pattern' => 'system/issue/(:any)',
        'load' => function (string $issue) {
            return [
                'component' => 'k-text-dialog',
                'props' => [
                    'text'         => $issue,
                    'submitButton' => t('retry')
                ]
            ];
        },
        'submit' => function () {
            return [
                'redirect' => '/system'
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
            // @codeCoverageIgnoreStart
            kirby()->system()->register(get('license'), get('email'));
            return [
                'event'   => 'system.register',
                'message' => t('license.register.success')
            ];
            // @codeCoverageIgnoreEnd
        }
    ],
];
