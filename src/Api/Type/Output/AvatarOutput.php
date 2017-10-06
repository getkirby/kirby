<?php

use Kirby\Users\User\Avatar;
use Kirby\Api\Type;

return function () {

    return [
        'name' => 'Avatar',
        'fields' => [
            'filename'  => [
                'type'    => Type::string(),
                'resolve' => function(Avatar $file) {
                    return $file->filename();
                },
            ],
            'name'  => [
                'type'    => Type::string(),
                'resolve' => function(Avatar $file) {
                    return $file->name();
                },
            ],
            'url'  => [
                'type'    => Type::string(),
                'resolve' => function(Avatar $file) {
                    return $file->url();
                },
            ],
            'extension'  => [
                'type'    => Type::string(),
                'resolve' => function(Avatar $file) {
                    return $file->extension();
                },
            ],
            'type' => [
                'type'    => Type::string(),
                'resolve' => function(Avatar $file) {
                    return $file->type();
                },
            ],
            'size'  => [
                'type'    => Type::int(),
                'resolve' => function(Avatar $file) {
                    return $file->size();
                },
            ],
            'niceSize'  => [
                'type'    => Type::string(),
                'resolve' => function(Avatar $file) {
                    return $file->niceSize();
                },
            ]
        ]
    ];

};
