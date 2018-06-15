<?php

use Kirby\Cms\Avatar;

/**
 * Avatar
 */
return [
    'fields' => [
        'dimensions' => function (Avatar $avatar) {
            return $avatar->dimensions()->toArray();
        },
        'exists' => function (Avatar $avatar) {
            return $avatar->exists();
        },
        'extension' => function (Avatar $avatar) {
            return $avatar->extension();
        },
        'filename' => function (Avatar $avatar) {
            return $avatar->filename();
        },
        'mime' => function (Avatar $avatar) {
            return $avatar->mime();
        },
        'modified' => function (Avatar $avatar) {
            return $avatar->modified('c');
        },
        'name' => function (Avatar $avatar) {
            return $avatar->name();
        },
        'niceSize' => function (Avatar $avatar) {
            return $avatar->niceSize();
        },
        'size' => function (Avatar $avatar) {
            return $avatar->size();
        },
        'url' => function (Avatar $avatar) {
            return $avatar->url();
        },
    ],
    'type'   => Avatar::class,
    'views'  => [
        'compact' => [
            'exists',
            'modified',
            'url',
        ]
    ],
];
