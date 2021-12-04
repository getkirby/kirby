<?php

use Kirby\Cms\File;
use Kirby\Form\Form;

/**
 * File
 */
return [
    'fields' => [
        'blueprint'  => fn (File $file) => $file->blueprint(),
        'content'    => fn (File $file) => Form::for($file)->values(),
        'dimensions' => fn (File $file) => $file->dimensions()->toArray(),
        'dragText'   => fn (File $file) => $file->panel()->dragText(),
        'exists'     => fn (File $file) => $file->exists(),
        'extension'  => fn (File $file) => $file->extension(),
        'filename'   => fn (File $file) => $file->filename(),
        'id'         => fn (File $file) => $file->id(),
        'link'       => fn (File $file) => $file->panel()->url(true),
        'mime'       => fn (File $file) => $file->mime(),
        'modified'   => fn (File $file) => $file->modified('c'),
        'name'       => fn (File $file) => $file->name(),
        'next'       => fn (File $file) => $file->next(),
        'nextWithTemplate' => function (File $file) {
            $files = $file->templateSiblings()->sorted();
            $index = $files->indexOf($file);

            return $files->nth($index + 1);
        },
        'niceSize'   => fn (File $file) => $file->niceSize(),
        'options'    => fn (File $file) => $file->panel()->options(),
        'panelIcon'  => function (File $file) {
            // TODO: remove in 3.7.0
            // @codeCoverageIgnoreStart
            deprecated('The API field file.panelIcon has been deprecated and will be removed in 3.7.0. Use file.panelImage instead');
            return $file->panel()->image();
        // @codeCoverageIgnoreEnd
        },
        'panelImage' => fn (File $file) => $file->panel()->image(),
        'panelUrl'   => fn (File $file) => $file->panel()->url(true),
        'prev'       => fn (File $file) => $file->prev(),
        'prevWithTemplate' => function (File $file) {
            $files = $file->templateSiblings()->sorted();
            $index = $files->indexOf($file);

            return $files->nth($index - 1);
        },
        'parent'     => fn (File $file) => $file->parent(),
        'parents'    => fn (File $file) => $file->parents()->flip(),
        'size'       => fn (File $file) => $file->size(),
        'template'   => fn (File $file) => $file->template(),
        'thumbs'     => function ($file) {
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
        'type'       => fn (File $file) => $file->type(),
        'url'        => fn (File $file) => $file->url(),
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
