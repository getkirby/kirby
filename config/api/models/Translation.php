<?php

use Kirby\Cms\Translation;

/**
 * Translation
 */
return [
    'fields' => [
        'data' => function (Translation $translation) {
            return $translation->data();
        },
        'direction' => function (Translation $translation) {
            return $translation->direction();
        },
        'id' => function (Translation $translation) {
            return $translation->id();
        },
        'name' => function (Translation $translation) {
            return $translation->name();
        },
        'translator' => function (Translation $translation) {
            return $translation->translator();
        },
    ],
    'type'  => Translation::class,
    'views' => [
        'compact' => [
            'direction',
            'id',
            'name'
        ]
    ]
];
