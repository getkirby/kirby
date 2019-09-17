<?php

use Kirby\Cms\Translation;

/**
 * Translation
 */
return [
    'fields' => [
        'author' => function (Translation $translation) {
            return $translation->author();
        },
        'data' => function (Translation $translation) {
            return $translation->dataWithFallback();
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
    ],
    'type'  => 'Kirby\Cms\Translation',
    'views' => [
        'compact' => [
            'direction',
            'id',
            'name'
        ]
    ]
];
