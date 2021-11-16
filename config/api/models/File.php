<?php

use Kirby\Cms\File;
use Kirby\Form\Form;

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
            return $file->panel()->dragText();
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
            return $file->panel()->url(true);
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
            $files = $file->templateSiblings()->sorted();
            $index = $files->indexOf($file);

            return $files->nth($index + 1);
        },
        'niceSize' => function (File $file) {
            return $file->niceSize();
        },
        'options' => function (File $file) {
            return $file->panel()->options();
        },
        'panelIcon' => function (File $file) {
            // TODO: remove in 3.7.0
            // @codeCoverageIgnoreStart
            deprecated('The API field file.panelIcon has been deprecated and will be removed in 3.7.0. Use file.panelImage instead');
            return $file->panel()->image();
        // @codeCoverageIgnoreEnd
        },
        'panelImage' => function (File $file) {
            return $file->panel()->image();
        },
        'panelUrl' => function (File $file) {
            return $file->panel()->url(true);
        },
        'prev' => function (File $file) {
            return $file->prev();
        },
        'prevWithTemplate' => function (File $file) {
            $files = $file->templateSiblings()->sorted();
            $index = $files->indexOf($file);

            return $files->nth($index - 1);
        },
        'parent' => function (File $file) {
            return $file->parent();
        },
        'parents' => function (File $file) {
            return $file->parents()->flip();
        },
        'size' => function (File $file) {
            return $file->size();
        },
        'template' => function (File $file) {
            return $file->template();
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
            return $file->url();
        },
    ],
    'type'  => 'Kirby\Cms\File',
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
