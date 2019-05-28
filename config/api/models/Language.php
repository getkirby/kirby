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
        'default' => function (Language $language) {
            return $language->isDefault();
        },
        'direction' => function (Language $language) {
            return $language->direction();
        },
        'locale' => function (Language $language) {
            return $language->locale();
        },
        'name' => function (Language $language) {
            return $language->name();
        },
        'rules' => function (Language $language) {
            return $language->rules();
        },
        'url' => function (Language $language) {
            return $language->url();
        },
    ],
    'type'  => Language::class,
    'views' => [
        'compact' => [
            'code',
            'default',
            'name',
            'rules',
            'direction'
        ]
    ]
];
