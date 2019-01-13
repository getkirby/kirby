<?php

use Kirby\Cms\FileVersion;

/**
 * FileVersion
 */
return [
    'fields' => [
        'dimensions' => function (FileVersion $file) {
            return $file->dimensions()->toArray();
        },
        'exists' => function (FileVersion $file) {
            return $file->exists();
        },
        'extension' => function (FileVersion $file) {
            return $file->extension();
        },
        'filename' => function (FileVersion $file) {
            return $file->filename();
        },
        'id' => function (FileVersion $file) {
            return $file->id();
        },
        'mime' => function (FileVersion $file) {
            return $file->mime();
        },
        'modified' => function (FileVersion $file) {
            return $file->modified('c');
        },
        'name' => function (FileVersion $file) {
            return $file->name();
        },
        'niceSize' => function (FileVersion $file) {
            return $file->niceSize();
        },
        'size' => function (FileVersion $file) {
            return $file->size();
        },
        'type' => function (FileVersion $file) {
            return $file->type();
        },
        'url' => function (FileVersion $file) {
            return $file->url(true);
        },
    ],
    'type'  => FileVersion::class,
    'views' => [
        'default' => [
            'dimensions',
            'exists',
            'extension',
            'filename',
            'id',
            'mime',
            'modified',
            'name',
            'niceSize',
            'size',
            'type',
            'url'
        ],
        'compact' => [
            'filename',
            'id',
            'type',
            'url',
        ],
        'panel' => [
            'dimensions',
            'extension',
            'filename',
            'id',
            'mime',
            'modified',
            'name',
            'niceSize',
            'template',
            'type',
            'url'
        ]
    ],
];
