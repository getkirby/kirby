<?php

use Kirby\Panel\Field;

return [
    // change title
    'site/changeTitle' => [
        'load' => function () {
            return [
                'component' => 'k-form-dialog',
                'props' => [
                    'fields' => [
                        'title' => Field::title()
                    ],
                    'submitButton' => t('rename'),
                    'value' => [
                        'title' => site()->title()->value()
                    ]
                ]
            ];
        },
        'submit' => function () {
            site()->changeTitle(get('title'));
            return [
                'event' => 'site.changeTitle',
            ];
        }
    ],
];
