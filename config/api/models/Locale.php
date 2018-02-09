<?php

use Kirby\Cms\Locale;

/**
 * User
 */
return [
    'fields' => [
        'data' => function (Locale $locale) {
            return $locale->data();
        },
        'direction' => function (Locale $locale) {
            return $locale->direction();
        },
        'id' => function (Locale $locale) {
            return $locale->id();
        },
        'name' => function (Locale $locale) {
            return $locale->name();
        },
        'translator' => function (Locale $locale) {
            return $locale->translator();
        },
    ],
    'type'  => Locale::class,
    'views' => [
        'compact' => [
            'direction',
            'id',
            'name'
        ]
    ]
];
