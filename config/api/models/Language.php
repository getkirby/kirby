<?php

use Kirby\Cms\Language;

/**
 * Language
 */
return [
    'fields' => [
        'code' => function (Language $language) {
            return $language->code();
        },
        'locale' => function (Language $language) {
            return $language->locale();
        },
        'name' => function (Language $language) {
            return $language->name();
        },
        'url' => function (Language $language) {
            return $language->url();
        },
    ],
    'type'  => Language::class,
    'views' => [
        'compact' => [
            'code',
            'name'
        ]
    ]
];
