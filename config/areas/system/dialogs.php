<?php

use Kirby\Cms\App;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;

return [
    // license registration
    'registration' => [
        'load' => function () {
            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'license' => [
                            'label'       => I18n::translate('license.register.label'),
                            'type'        => 'text',
                            'required'    => true,
                            'counter'     => false,
                            'placeholder' => 'K3-',
                            'help'        => I18n::translate('license.register.help')
                        ],
                        'email' => Field::email([
                            'required' => true
                        ])
                    ],
                    'submitButton' => I18n::translate('license.register'),
                    'value' => [
                        'license' => null,
                        'email'   => null
                    ]
                ]
            ];
        },
        'submit' => function () {
            // @codeCoverageIgnoreStart
            $kirby = App::instance();
            $kirby->system()->register(
                $kirby->request()->get('license'),
                $kirby->request()->get('email')
            );

            return [
                'event'   => 'system.register',
                'message' => I18n::translate('license.register.success')
            ];
            // @codeCoverageIgnoreEnd
        }
    ],
];
