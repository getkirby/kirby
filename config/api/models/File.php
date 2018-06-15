<?php

use Kirby\Cms\File;
use Kirby\Cms\Form;

/**
 * File
 */
return [
    'fields' => [
        'blueprint' => function (File $file) {
            return $file->blueprint();
        },
        'content' => function (File $file) {
            return $file->content()->toArray();
        },
        'dimensions' => function (File $file) {
            return $file->dimensions()->toArray();
        },
        'exists' => function (File $avatar) {
            return $avatar->exists();
        },
        'extension' => function (File $file) {
            return $file->extension();
        },
        'filename' => function (File $file) {
            return $file->filename();
        },
        'id' => function (File $file) {
            return $file->id();
        },
        'mime' => function (File $file) {
            return $file->mime();
        },
        'modified' => function (File $file) {
            return $file->modified('c');
        },
        'name' => function (File $file) {
            return $file->name();
        },
        'next' => function (File $file) {
            return $file->next();
        },
        'options' => function (File $file) {
            return $file->blueprint()->options()->toArray();
        },
        'prev' => function (File $file) {
            return $file->prev();
        },
        'niceSize' => function (File $file) {
            return $file->niceSize();
        },
        'parent' => function (File $file) {
            return $file->parent();
        },
        'parents' => function (File $file) {
            return $file->parents()->flip();
        },
        'template' => function (File $file) {
            return $file->template();
        },
        'size' => function (File $file) {
            return $file->size();
        },
        'type' => function (File $file) {
            return $file->type();
        },
        'url' => function (File $file) {
            return $file->url();
        },
    ],
    'type'  => File::class,
    'views' => [
        'default' => [
            'content',
            'dimensions',
            'exists',
            'extension',
            'filename',
            'id',
            'mime',
            'modified',
            'name',
            'next' => 'compact',
            'niceSize',
            'parent' => 'compact',
            'options',
            'prev' => 'compact',
            'size',
            'template',
            'type',
            'url'
        ],
        'compact' => [
            'filename',
            'id',
            'type',
            'url'
        ],
        'panel' => [
            'blueprint',
            'dimensions',
            'extension',
            'filename',
            'id',
            'mime',
            'modified',
            'name',
            'next' => 'compact',
            'niceSize',
            'parent' => 'compact',
            'parents' => ['id', 'slug', 'title'],
            'prev' => 'compact',
            'template',
            'type',
            'url'
        ]
    ],
];
