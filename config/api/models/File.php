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
            return Form::for($file)->values();
        },
        'dimensions' => function (File $file) {
            return $file->dimensions()->toArray();
        },
        'dragText' => function (File $file) {
            return $file->dragText();
        },
        'exists' => function (File $file) {
            return $file->exists();
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
        'link' => function (File $file) {
            return $file->panelUrl(true);
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
        'nextWithTemplate' => function (File $file) {
            $files = $file->templateSiblings()->sortBy('sort', 'asc');
            $index = $files->indexOf($file);

            return $files->nth($index + 1);
        },
        'options' => function (File $file) {
            return $file->permissions()->toArray();
        },
        'panelIcon' => function (File $file) {
            return $file->panelIcon();
        },
        'panelImage' => function (File $file) {
            return $file->panelImage();
        },
        'panelUrl' => function (File $file) {
            return $file->panelUrl(true);
        },
        'prev' => function (File $file) {
            return $file->prev();
        },
        'prevWithTemplate' => function (File $file) {
            $files = $file->templateSiblings()->sortBy('sort', 'asc');
            $index = $files->indexOf($file);

            return $files->nth($index - 1);
        },
        'niceSize' => function (File $file) {
            return $file->niceSize();
        },
        'panelIcon' => function (File $file) {
            return $file->panelIcon();
        },
        'panelImage' => function (File $file) {
            return $file->panelImage();
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
        'thumbs' => function ($file) {
            if ($file->isResizable() === false) {
                return null;
            }

            return [
                'tiny'   => $file->resize(128)->url(),
                'small'  => $file->resize(256)->url(),
                'medium' => $file->resize(512)->url(),
                'large'  => $file->resize(768)->url(),
                'huge'   => $file->resize(1024)->url(),
            ];
        },
        'type' => function (File $file) {
            return $file->type();
        },
        'url' => function (File $file) {
            return $file->url(true);
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
            'link',
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
            'link',
            'type',
            'url',
        ],
        'panel' => [
            'blueprint',
            'content',
            'dimensions',
            'extension',
            'filename',
            'id',
            'link',
            'mime',
            'modified',
            'name',
            'nextWithTemplate' => 'compact',
            'niceSize',
            'options',
            'panelIcon',
            'panelImage',
            'parent' => 'compact',
            'parents' => ['id', 'slug', 'title'],
            'prevWithTemplate' => 'compact',
            'template',
            'type',
            'url'
        ]
    ],
];
