<?php

use Kirby\Api\Type;

return function ($site) {
    return [
        'description' => 'Fetches a Page object by id',
        'type'        => Type::page(),
        'args'        => [
            'id' => [
                'type' => Type::string()
            ]
        ],
        'resolve' => function ($root, $args) use ($site) {
            return $site->find($args['id']);
        }
    ];
};
