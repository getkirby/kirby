<?php

use Kirby\Cms\File;
use Kirby\Api\Type;

return function () {

    return [
        'name' => 'File',
        'fields' => [
            'filename'  => [
                'type'    => Type::string(),
                'resolve' => function(File $file) {
                    return $file->filename();
                },
            ],
            'name'  => [
                'type'    => Type::string(),
                'resolve' => function(File $file) {
                    return $file->name();
                },
            ],
            'url'  => [
                'type'    => Type::string(),
                'resolve' => function(File $file) {
                    return $file->url();
                },
            ],
            'extension'  => [
                'type'    => Type::string(),
                'resolve' => function(File $file) {
                    return $file->extension();
                },
            ],
            'type' => [
                'type'    => Type::string(),
                'resolve' => function(File $file) {
                    return $file->type();
                },
            ],
            'size'  => [
                'type'    => Type::int(),
                'resolve' => function(File $file) {
                    return $file->size();
                },
            ],
            'niceSize'  => [
                'type'    => Type::string(),
                'resolve' => function(File $file) {
                    return $file->niceSize();
                },
            ],
            'page' => [
                'type' => Type::page(),
                'resolve' => function (File $file) {
                    return $file->page();
                }
            ]
        ]
    ];

};
