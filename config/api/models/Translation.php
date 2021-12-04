<?php

use Kirby\Cms\Translation;

/**
 * Translation
 */
return [
    'fields' => [
        'author'    => fn (Translation $translation) => $translation->author(),
        'data'      => fn (Translation $translation) => $translation->dataWithFallback(),
        'direction' => fn (Translation $translation) => $translation->direction(),
        'id'        => fn (Translation $translation) => $translation->id(),
        'name'      => fn (Translation $translation) => $translation->name(),
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
