<?php

use Kirby\Api\Type;

return function ($site) {
    return [
        'description' => 'Fetches multiple pages by an array of ids',
        'type'        => Type::listOf(Type::page()),
        'args'        => [
            'id' => [
                'type' => Type::listOf(Type::string())
            ]
        ],
        'resolve' => function ($root, $args) use ($site) {
            return $site->children()->find(...$args['id']);
        }
    ];
};
