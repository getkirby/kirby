<?php

use Kirby\Api\Type;

return function () {

    return [
        'name' => 'Site',
        'fields' => [
            'title' => [
                'type' => Type::string(),
                'resolve' => function($site) {
                    return $site->title()->value();
                }
            ],
            'url' => [
                'type' => Type::string(),
                'resolve' => function($site) {
                    return $site->url();
                }
            ],
            'children' => [
                'type'    => Type::listOf(Type::page()),
                'resolve' => function($site, $args = []) {
                    return $site->children()->toArray();
                }
            ]
        ]
    ];

};
